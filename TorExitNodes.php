<?php

/**
 * Prevents Tor exit nodes from editing a wiki.
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
 * @link http://www.mediawiki.org/wiki/Extension:TorBlock Documentation
 *
 * @author Andrew Garrett <andrew@epstone.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0$
 */

/**
 * Collection of functions maintaining the list of Tor exit nodes.
 */
class TorExitNodes {

	static protected $mExitNodes = null;

	/**
	 * Determine if a given IP is a Tor exit node.
	 *
	 * @param string|null $ip The IP address to check, or null to use the request IP
	 * @return bool True if an exit node, false otherwise
	 */
	public static function isExitNode( $ip = null ) {
		if ( $ip == null ) {
			$ip = RequestContext::getMain()->getRequest()->getIP();
		}

		$nodes = self::getExitNodes();
		return in_array( $ip, $nodes );
	}

	/**
	 * Get the array of Tor exit nodes. First try the cache, then query
	 * the source.
	 *
	 * @return array Tor exit nodes
	 */
	public static function getExitNodes() {
		if ( is_array( self::$mExitNodes ) ) {
			wfDebug( "Loading Tor exit node list from memory.\n" );
			return self::$mExitNodes;
		}

		global $wgMemc;

		$nodes = $wgMemc->get( 'mw-tor-exit-nodes' ); // No use of wfMemcKey because it should be multi-wiki.

		if ( is_array( $nodes ) ) {
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
				if ( is_array( $nodes ) ) {
					return self::$mExitNodes = $nodes;
				} else {
					wfDebug( "Tried very hard to get the Tor list since mw-tor-list-status says it is loaded, to no avail.\n" );
					return array();
				}
			}
		}

		// We have to actually load from the server.

		global $wgTorLoadNodes;
		if ( !$wgTorLoadNodes ) {
			// Disabled.
			wfDebug( "Unable to load Tor exit node list: cold load disabled on page-views.\n" );
			return array();
		}

		wfDebug( "Loading Tor exit node list cold.\n" );

		self::loadExitNodes();
		return self::$mExitNodes;
	}

	/**
	 * Load the list of Tor exit nodes from the source and cache it
	 * for future use.
	 */
	public static function loadExitNodes() {
		global $wgMemc;
		wfProfileIn( __METHOD__ );

		// Set loading key, to prevent DoS of server.
		$wgMemc->set( 'mw-tor-list-status', 'loading', intval( ini_get( 'max_execution_time' ) ) );

		$nodes = self::loadExitNodes_Onionoo();
		if( !$nodes ) {
			$nodes = self::loadExitNodes_BulkList();
		}

		self::$mExitNodes = $nodes;

		// Save to cache
		$wgMemc->set( 'mw-tor-exit-nodes', $nodes, 1800 ); // Store for half an hour.
		$wgMemc->set( 'mw-tor-list-status', 'loaded', 1800 );

		wfProfileOut( __METHOD__ );
	}

	/**
	 * Get the list of Tor exit nodes from the Tor Project's website.
	 *
	 * @return array Tor exit nodes
	 */
	protected static function loadExitNodes_BulkList() {
		wfProfileIn( __METHOD__ );

		global $wgTorIPs;

		$nodes = array();
		foreach ( $wgTorIPs as $ip ) {
			$url = 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=' . $ip;
			$data = Http::get( $url, 'default', array( 'sslVerifyCert' => false ) );
			$lines = explode( "\n", $data );

			foreach ( $lines as $line ) {
				if ( strpos( $line, '#' ) === false ) {
					$nodes[trim($line)] = true;
				}
			}
		}
		$nodes = array_keys( $nodes );

		wfProfileOut( __METHOD__ );
		return $nodes;
	}

	/**
	 * Get the list of Tor exit nodes using the Onionoo protocol with the
	 * server specified in the configuration.
	 *
	 * @return array Tor exit nodes
	 */
	protected static function loadExitNodes_Onionoo() {
		wfProfileIn( __METHOD__ );

		global $wgTorOnionooServer;
		$nodes = array();
		$url = wfExpandUrl( "$wgTorOnionooServer/summary", PROTO_HTTPS );
		$raw = Http::get( $url, 'default', array( 'sslVerifyCert' => false ) );
		$data = FormatJson::decode( $raw, true );

		$nodes = array();
		foreach ( $data['relays'] as $relay ) {
			foreach ( $relay['a'] as $ip ) {
				$nodes[$ip] = true;
			}
		}
		$nodes = array_keys( $nodes );

		wfProfileOut( __METHOD__ );
		return $nodes;
	}
}
