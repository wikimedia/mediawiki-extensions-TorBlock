<?php

/**
 * Hooks for the Extension:TorBlock for MediaWiki
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

class TorBlock {
	public static $mExitNodes;

	/**
	 * Check if a user is a Tor node and not whitelisted or allowed
	 * to bypass tor blocks.
	 *
	 * @param Title $title Title being acted upon
	 * @param User $user User performing the action
	 * @param string $action Action being performed
	 * @param array &$result Will be filled with block status if blocked
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive( &$title, &$user, $action, &$result ) {
		global $wgTorAllowedActions, $wgRequest;
		if ( in_array( $action, $wgTorAllowedActions ) ) {
			return true;
		}

		wfDebug( "Checking Tor status\n" );

		if ( self::isExitNode() ) {
			wfDebug( "User detected as editing through tor." );

			global $wgTorBypassPermissions;
			foreach ( $wgTorBypassPermissions as $perm) {
				if ( $user->isAllowed( $perm ) ) {
					wfDebug( "User has $perm permission. Exempting from Tor Blocks" );
					return true;
				}
			}

			$ip = $wgRequest->getIP();
			if ( Block::isWhitelistedFromAutoblocks( $ip ) ) {
				wfDebug( "IP is in autoblock whitelist. Exempting from Tor blocks." );
				return true;
			}

			wfDebug( "User detected as editing from Tor node. Adding Tor block to permissions errors." );

			$result = array( 'torblock-blocked', $ip );

			return false;
		}

		return true;
	}

	/**
	 * Check if the user is logged in from a Tor exit node but is not exempt.
	 * If so, block the user.
	 *
	 * @param User $user User sending email
	 * @param string $editToken Edit token supplied
	 * @param array &$hookError Will be filled with block information
	 * @return bool
	 */
	public static function onEmailUserPermissionsErrors( $user, $editToken, &$hookError ) {
		wfDebug( "Checking Tor status" );

		// Just in case we're checking another user
		global $wgUser, $wgRequest;
		if ( $user->getName() != $wgUser->getName() ) {
			return true;
		}

		if ( self::isExitNode() ) {
			wfDebug( "User detected as editing through tor." );

			global $wgTorBypassPermissions;
			foreach ( $wgTorBypassPermissions as $perm) {
				if ( $user->isAllowed( $perm ) ) {
					wfDebug( "User has $perm permission. Exempting from Tor Blocks." );
					return true;
				}
			}

			$ip = $wgRequest->getIP();
			if ( Block::isWhitelistedFromAutoblocks( $ip ) ) {
				wfDebug( "IP is in autoblock whitelist. Exempting from Tor blocks." );
				return true;
			}

			wfDebug( "User detected as editing from Tor node. Denying email." );

			$hookError = array( 'permissionserrors', 'torblock-blocked', array( $ip ) );
			return false;
		}

		return true;
	}

	/**
	 * Set a variable for Extension:AbuseFilter indicating whether the
	 * user is operating from a tor exit node or not.
	 *
	 * @param AbuseFilterVariableHolder &$vars Variable holder for AbuseFilter
	 * @param Title $title Title being viewed
	 * @return bool
	 */
	public static function onAbuseFilterFilterAction( &$vars, $title ) {
		$vars->setVar( 'tor_exit_node', self::isExitNode() ? 1 : 0 );
		return true;
	}

	/**
	 * Set a variable for Extension:AbuseFilter indicating whether the
	 * user is operating from a tor exit node or not.
	 *
	 * @param array $builder Array of builder values
	 * @return bool
	 */
	public static function onAbuseFilterBuilder( array &$builder ) {
		$builder['vars']['tor_exit_node'] = 'tor-exit-node';
		return true;
	}

	/**
	 * @return array|mixed
	 */
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
		} else {
			$liststatus = $wgMemc->get( 'mw-tor-list-status' );
			if ( $liststatus == 'loading' ) {
				// Somebody else is loading it.
				wfDebug( "Old Tor list expired and we are still loading the new one.\n" );
				return array();
			} elseif ( $liststatus == 'loaded' ) {
				$nodes = $wgMemc->get( 'mw-tor-exit-nodes' );
				if (is_array($nodes)) {
					return self::$mExitNodes = $nodes;
				} else {
					wfDebug( "Tried very hard to get the Tor list since mw-tor-list-status says it is loaded, to no avail.\n" );
					return array();
				}
			}
		}

		// We have to actually load from the server.

		global $wgTorLoadNodes;
		if (!$wgTorLoadNodes) {
			// Disabled.
			wfDebug( "Unable to load Tor exit node list: cold load disabled on page-views.\n" );
			return array();
		}

		wfDebug( "Loading Tor exit node list cold.\n" );

		return self::loadExitNodes();
	}

	/**
	 * @return array
	 */
	public static function loadExitNodes() {
		wfProfileIn( __METHOD__ );

		global $wgTorIPs, $wgMemc;

		// Set loading key, to prevent DoS of server.

		$wgMemc->set( 'mw-tor-list-status', 'loading', 300 );

		$nodes = array();
		foreach ( $wgTorIPs as $ip ) {
			$nodes = array_unique( array_merge( $nodes, self::loadNodesForIP( $ip ) ) );
		}

		// Save to cache.
		$wgMemc->set( 'mw-tor-exit-nodes', $nodes, 1800 ); // Store for half an hour.
		$wgMemc->set( 'mw-tor-list-status', 'loaded', 1800 );

		wfProfileOut( __METHOD__ );

		return self::$mExitNodes = $nodes;
	}

	/**
	 * @param $ip
	 * @return array
	 */
	public static function loadNodesForIP( $ip ) {
		$url = 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=' . $ip;
		$data = Http::get( $url, 'default', array( 'sslVerifyCert' => false ) );
		$lines = explode("\n", $data);

		$nodes = array();
		foreach ( $lines as $line ) {
			if ( strpos( $line, '#' ) === false ) {
				$nodes[] = trim($line);
			}
		}

		return $nodes;
	}

	/**
	 * @param null $ip
	 * @return bool
	 */
	public static function isExitNode( $ip = null ) {
		if ( $ip == null ) {
			global $wgRequest;
			$ip = $wgRequest->getIP();
		}

		$nodes = self::getExitNodes();

		return in_array( $ip, $nodes );
	}

	/**
	 * When loading the user's blocked status, if they are operating as
	 * a Tor exit node, ignore other blocks.
	 *
	 * @param User $user User checking status for
	 * @return bool
	 */
	public static function onGetBlockedStatus( &$user ) {
		global $wgTorDisableAdminBlocks;
		if (
			$wgTorDisableAdminBlocks &&
			self::isExitNode() &&
			$user->mBlock instanceof Block &&
			$user->mBlock->getType() != Block::TYPE_USER
		) {
			wfDebug( "User using Tor node. Disabling IP block as it was probably targetted at the tor node." );
			// Node is probably blocked for being a Tor node. Remove block.
			$user->mBlockedby = 0;
		}

		return true;
	}

	/**
	 * If an IP address is an exit node, stop it from beign autoblocked.
	 *
	 * @param string $autoblockip IP address being blocked
	 * @param Block &$block Block being applied
	 * @return bool
	 */
	public static function onAbortAutoblock( $autoblockip, Block &$block ) {
		return !self::isExitNode( $autoblockip );
	}

	/**
	 * When the user is a Tor exit node, make sure they meet configured
	 * age/edit count requirements before allowing promotions.
	 *
	 * @param User $user User being promoted
	 * @param array &$promote Groups being added
	 * @return bool
	 */
	public static function onGetAutoPromoteGroups( User $user, array &$promote ) {
		global $wgTorAutoConfirmAge, $wgTorAutoConfirmCount;

		// Check against stricter requirements for tor nodes.
		// Counterintuitively, we do the requirement checks first.
		// This is so that we don't have to hit memcached to get the
		// exit list, unnecessarily.

		if ( !count( $promote ) ) {
			// No groups to promote to anyway
			return true;
		}

		$age = time() - wfTimestampOrNull( TS_UNIX, $user->getRegistration() );

		if ( $age >= $wgTorAutoConfirmAge && $user->getEditCount() >= $wgTorAutoConfirmCount ) {
			// Does match requirements. Don't bother checking if we're an exit node.
			return true;
		}

		if ( self::isExitNode() ) {
			// Tor user, doesn't match the expanded requirements.
			$promote = array();
		}

		return true;
	}

	/**
	 * Check if a user is a Tor node if the wiki is configured
	 * to autopromote on Tor status.
	 *
	 * @param int $type Condition being checked
	 * @param array $args Arguments passed to the condition
	 * @param User $user User being promoted
	 * @param bool &$result Will be filled with result of condition
	 * @return bool
	 */
	public static function onAutopromoteCondition( $type, array $args, User $user, &$result ) {
		if ( $type == APCOND_TOR ) {
			$result = self::isExitNode();
		}

		return true;
	}

	/**
	 * If enabled, tag recent changes made by a Tor exit node.
	 *
	 * @param RecentChange &$recentChange The change being saved
	 * @return bool true
	 */
	public static function onRecentChangeSave( RecentChange &$recentChange ) {
		global $wgTorTagChanges;

		if ( class_exists('ChangeTags') && $wgTorTagChanges && self::isExitNode() ) {
			ChangeTags::addTags( 'tor', $recentChange->mAttribs['rc_id'], $recentChange->mAttribs['rc_this_oldid'], $recentChange->mAttribs['rc_logid'] );
		}
		return true;
	}

	/**
	 * If enabled, add a new tag type for recent changes made by Tor exit nodes.
	 *
	 * @param array $emptyTags List of defined tags
	 * @return bool true
	 */
	public static function onListDefinedTags( array &$emptyTags ) {
		global $wgTorTagChanges;

		if ( $wgTorTagChanges ) {
			$emptyTags[] = 'tor';
		}
		return true;
	}

	/**
	 * Creates a message with the Tor blocking status if applicable.
	 *
	 * @param array $msg Message with the status
	 * @param string $ip The IP address to be checked
	 * @return boolean true
	 */
	public static function getTorBlockStatus( array &$msg, $ip ) {
		// IP addresses can be blocked only
		// Fast return if IP is not an exit node
		if ( IP::isIPAddress( $ip ) && self::isExitNode( $ip ) ) {
			$msg[] = Html::rawElement(
				'span',
				array( 'class' => 'mw-torblock-isexitnode' ),
				wfMessage( 'torblock-isexitnode', $ip )->parse()
			);
		}
		return true;
	}
}
