<?php
/**
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
 * https://www.gnu.org/copyleft/gpl.html
 *
 */

namespace MediaWiki\Extension\TorBlock\Tests;

use MediaWiki\Extension\TorBlock\TorExitNodes;
use MediaWikiIntegrationTestCase;

/**
 * @group TorBlock
 * @covers \MediaWiki\Extension\TorBlock\TorExitNodes
 */
class TorBlockTest extends MediaWikiIntegrationTestCase {
	public static function provideIPList() {
		return [
			'IPv4 not in list' => [ '127.0.0.1', false ],
			'IPv6 not in list' => [ '::1', false ],
			'Non-IP addresses' => [ 'not an IP address', false ],
		];
	}

	/**
	 * @dataProvider provideIPList
	 */
	public function testIfExitNode( $ip, $res ) {
		$this->assertSame( $res, TorExitNodes::isExitNode( $ip ) );
	}
}
