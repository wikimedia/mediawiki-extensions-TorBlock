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
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Collection of functions maintaining the list of Tor exit nodes.
 */
class TorExitNodes {
	const CACHE_NORMAL = 'cached';
	const CACHE_REFRESH = 'refresh';

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

		return in_array( IP::sanitizeIP( $ip ), self::getExitNodes() );
	}

	/**
	 * Get the array of Tor exit nodes. First try the cache, then query the source.
	 *
	 * @param string $mode Class CACHE_* constant
	 * @return array Tor exit nodes
	 */
	public static function getExitNodes( $mode = self::CACHE_NORMAL ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		return $cache->getWithSetCallback(
			$cache->makeGlobalKey( 'mw-tor-exit-nodes' ),
			$cache::TTL_DAY,
			function () {
				global $wgTorLoadNodes;

				if ( $wgTorLoadNodes ) {
					wfDebugLog( 'torblock', "Loading Tor exit node list cold." );
					$value = self::loadExitNodes_Onionoo() ?: self::loadExitNodes_BulkList();
				} else {
					$value = []; // disabled
				}

				return $value;
			},
			( $mode === self::CACHE_REFRESH )
				? [ 'minAsOf' => INF ] // force a cache regeneration
				: [
					// Avoid excess cache server I/O via process caching
					'pcTTL' => $cache::TTL_PROC_LONG,
					'pcGroup' => 'tor-exit-nodes:1',
					// Avoid stampedes on TOR list servers due to cache expiration
					'lockTSE' => 30,
					'staleTTL' => $cache::TTL_MINUTE,
					 // Avoid stampedes on TOR list servers due to cache eviction
					'busyValue' => []
				]
		);
	}

	/**
	 * Load the list of Tor exit nodes from the source and cache it for future use
	 *
	 * @return array Tor exit nodes
	 */
	public static function loadExitNodes() {
		return self::getExitNodes( self::CACHE_REFRESH );
	}

	/**
	 * Get the list of Tor exit nodes from the Tor Project's website.
	 *
	 * @return array Tor exit nodes
	 */
	protected static function loadExitNodes_BulkList() {
		global $wgTorIPs, $wgTorProjectCA, $wgTorBlockProxy;

		$options = [
			'caInfo' => is_readable( $wgTorProjectCA ) ? $wgTorProjectCA : null
		];
		if ( $wgTorBlockProxy ) {
			$options['proxy'] = $wgTorBlockProxy;
		}

		$nodes = [];
		foreach ( $wgTorIPs as $ip ) {
			$url = 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=' . $ip;
			$data = Http::get( $url, $options, __METHOD__ );
			$lines = explode( "\n", $data );

			foreach ( $lines as $line ) {
				if ( strpos( $line, '#' ) === false ) {
					$nodes[trim( $line )] = true;
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

		$url = wfExpandUrl( "$wgTorOnionooServer/details?type=relay&running=true&flag=Exit",
			PROTO_HTTPS );
		$options = [
			'caInfo' => is_readable( $wgTorOnionooCA ) ? $wgTorOnionooCA : null
		];
		if ( $wgTorBlockProxy ) {
			$options['proxy'] = $wgTorBlockProxy;
		}
		$raw = Http::get( $url, $options, __METHOD__ );
		$data = FormatJson::decode( $raw, true );

		if ( !isset( $data['relays'] ) ) {
			wfDebugLog( 'torblock', "Got no reply or an invalid reply from Onionoo.\n" );
			return [];
		}

		$nodes = [];
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
				// @phan-suppress-next-line PhanTypeArraySuspicious false positive
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
