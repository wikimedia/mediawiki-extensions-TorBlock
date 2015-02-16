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
 * @link https://www.mediawiki.org/wiki/Extension:TorBlock Documentation
 *
 * @author Andrew Garrett <andrew@epstone.net>
 * @license http://www.gnu.org/copyleft/gpl.html  GNU General Public License 2.0 or later
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
		return in_array( IP::sanitizeIP( $ip ), $nodes );
	}

	/**
	 * Get the array of Tor exit nodes. First try the cache, then query
	 * the source.
	 *
	 * @return array Tor exit nodes
	 */
	public static function getExitNodes() {
		if ( is_array( self::$mExitNodes ) ) {
			// wfDebugLog( 'torblock', "Loading Tor exit node list from memory.\n" );
			return self::$mExitNodes;
		}

		global $wgMemc;

		$nodes = $wgMemc->get( 'mw-tor-exit-nodes' ); // No use of wfMemcKey because it should be multi-wiki.

		if ( is_array( $nodes ) ) {
			// wfDebugLog( 'torblock', "Loading Tor exit node list from memcached.\n" );
			// Lucky.
			return self::$mExitNodes = $nodes;
		} else {
			$liststatus = $wgMemc->get( 'mw-tor-list-status' );
			if ( $liststatus == 'loading' ) {
				// Somebody else is loading it.
				wfDebugLog( 'torblock', "Old Tor list expired and we are still loading the new one.\n" );
				return array();
			} elseif ( $liststatus == 'loaded' ) {
				$nodes = $wgMemc->get( 'mw-tor-exit-nodes' );
				if ( is_array( $nodes ) ) {
					return self::$mExitNodes = $nodes;
				} else {
					wfDebugLog( 'torblock', "Tried very hard to get the Tor list since mw-tor-list-status says it is loaded, to no avail.\n" );
					return array();
				}
			}
		}

		// We have to actually load from the server.

		global $wgTorLoadNodes;
		if ( !$wgTorLoadNodes ) {
			// Disabled.
			// wfDebugLog( 'torblock', "Unable to load Tor exit node list: cold load disabled on page-views.\n" );
			return array();
		}

		wfDebugLog( 'torblock', "Loading Tor exit node list cold.\n" );

		self::loadExitNodes();
		return self::$mExitNodes;
	}

	/**
	 * Load the list of Tor exit nodes from the source and cache it
	 * for future use.
	 */
	public static function loadExitNodes() {
		global $wgMemc;

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
	}

	/**
	 * Get the list of Tor exit nodes from the Tor Project's website.
	 *
	 * @return array Tor exit nodes
	 */
	protected static function loadExitNodes_BulkList() {
		global $wgTorIPs, $wgTorProjectCA, $wgTorBlockProxy;

		$options = array(
			'caInfo' => is_readable( $wgTorProjectCA ) ? $wgTorProjectCA : null
		);
		if ( $wgTorBlockProxy ) {
			$options['proxy'] = $wgTorBlockProxy;
		}

		$nodes = array();
		foreach ( $wgTorIPs as $ip ) {
			$url = 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=' . $ip;
			$data = Http::get( $url, 'default', $options );
			$lines = explode("\n", $data);

			foreach ( $lines as $line ) {
				if ( strpos( $line, '#' ) === false ) {
					$nodes[trim($line)] = true;
				}
			}
		}
		return array_keys( $nodes );
	}

	/**
	 * Get the list of Tor exit nodes using the Onionoo protocol with the
	 * server specified in the configuration.
	 *
	 * @return string[] Tor exit nodes
	 */
	protected static function loadExitNodes_Onionoo() {
		global $wgTorOnionooServer, $wgTorOnionooCA, $wgTorBlockProxy;
		$url = wfExpandUrl( "$wgTorOnionooServer/details?type=relay&running=true&flag=Exit", PROTO_HTTPS );
		$options = array(
			'caInfo' => is_readable( $wgTorOnionooCA ) ? $wgTorOnionooCA : null
		);
		if ( $wgTorBlockProxy ) {
			$options['proxy'] = $wgTorBlockProxy;
		}
		$raw = Http::get( $url, 'default', $options );
		$data = FormatJson::decode( $raw, true );

		if ( !isset( $data['relays'] ) ) {
			wfDebugLog( 'torblock', "Got no reply or an invalid reply from Onionoo.\n" );
			return array();
		}

		$nodes = array();
		foreach ( $data['relays'] as $relay ) {
			$addresses = $relay['or_addresses'];
			if ( isset( $relay['exit_addresses'] ) ) {
				$addresses = array_merge( $addresses, $relay['exit_addresses'] );
			}

			foreach ( $addresses as $ip ) {
				// Trim the port if it has one.
				$portPosition = strrpos( $ip, ':' );
				if ( $portPosition !== false ) {
					$ip = substr( $ip, 0, $portPosition );
				}

				// Trim surrounding brackets for IPv6 addresses.
				$hasBrackets = $ip[0] == '[';
				if ( $hasBrackets ) {
					$ip = substr( $ip, 1, -1 );
				}

				if ( !IP::isValid( $ip ) ) {
					wfDebug( 'Invalid IP address in Onionoo response.' );
					continue;
				}

				$nodes[IP::sanitizeIP( $ip )] = true;
			}
		}
		return array_keys( $nodes );
	}
}
