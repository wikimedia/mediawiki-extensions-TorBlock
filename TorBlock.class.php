<?php
if ( ! defined( 'MEDIAWIKI' ) )
	die();

class TorBlock {
	public static $mExitNodes;

	public static function onGetUserPermissionsErrorsExpensive( &$title, &$user, &$action, &$result ) {
		global $wgTorAllowedActions;
		if (in_array( $action, $wgTorAllowedActions)) {
			return true;
		}

		wfDebug( "Checking Tor status\n" );

		if (self::isExitNode()) {
			wfDebug( "-User detected as editing through tor.\n" );

			global $wgTorBypassPermissions;
			foreach( $wgTorBypassPermissions as $perm) {
				if ($user->isAllowed( $perm )) {
					wfDebug( "-User has $perm permission. Exempting from Tor Blocks\n" );
					return true;
				}
			}

			$ip = wfGetIp();
			wfDebug( "-User detected as editing from Tor node. Adding Tor block to permissions errors\n" );
			wfLoadExtensionMessages( 'TorBlock' );

			$result[] = array('torblock-blocked', $ip);

			return false;
		}

		return true;
	}

	public static function getExitNodes() {

		if (is_array(self::$mExitNodes)) {
			wfDebug( "Loading Tor exit node list from memory.\n" );
			return self::$mExitNodes;
		}

		global $wgMemc;

		$nodes = $wgMemc->get( 'mw-tor-exit-nodes' ); // No use of wfMemcKey because it should be multi-wiki.

		if (is_array($nodes)) {
			wfDebug( "Loading Tor exit node list from memcached.\n" );
			// Lucky.
			return self::$mExitNodes = $nodes;
		}

		// We have to actually load them.

		if (!$wgTorLoadNodes) {
			// Disabled.
			wfDebug( "Unable to load Tor exit node list: cold load disabled on page-views.\n" );
			return array();
		}

		wfDebug( "Loading Tor exit node list cold.\n" );

		return self::$mExitNodes = self::loadExitNodes();
	}

	public static function loadExitNodes() {
		$nodes = array();

		global $wgTorIPs;

		foreach( $wgTorIPs as $ip ) {
			$nodes = array_unique( array_merge( $nodes, self::getExitList( $ip ) ) );
		}

		global $wgMemc;

		$wgMemc->set( 'mw-tor-exit-nodes', $nodes, 1800 ); // Store for half an hour.

		return $nodes;
	}

	public static function getExitList( $ip ) {
		$url = 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip='.$ip;

		$data = Http::get( $url );

		$lines = split("\n", $data);

		$nodes = array();

		foreach( $lines as $line ) {
			if (strpos( $line, '#' )===false) {
				$nodes[] = trim($line);
			}
		}

		return $nodes;
	}

	public static function isExitNode($ip = null) {
		#return true; ## FOR DEBUGGING
		if ($ip == null) {
			$ip = wfGetIp();
		}

		$nodes = self::getExitNodes();

		return in_array( $ip, $nodes );
	}

	public static function onGetBlockedStatus( &$user ) {
		if (self::isExitNode() && $user->mBlock && !$user->mBlock->mUser) {
			wfDebug( "User using Tor node. Disabling IP block as it was probably targetted at the tor node." );
			// Node is probably blocked for being a Tor node. Remove block.
			$user->mBlockedBy = 0;
		}

		return true;
	}

	public static function onAbortAutoblock( $autoblockip, &$block ) {
		return !self::isExitNode( $autoblockip );
	}

	public static function onGetAutoPromoteGroups( $user, &$promote ) {
		if (self::isExitNode()) {
			// Check against stricter requirements.
			$age = time() - wfTimestampOrNull( TS_UNIX, $user->getRegistration() );

			global $wgTorAutoConfirmAge, $wgTorAutoConfirmCount;

			if ($age < $wgTorAutoConfirmAge || $user->getEditCount() < $wgTorAutoConfirmCount) {
				// No!
				$promote = array();
			}
		}

		return true;
	}
}
