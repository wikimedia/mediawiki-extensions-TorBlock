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

use MediaWiki\Tests\Api\ApiTestCase;

/**
 * @group API
 *
 * @covers \MediaWiki\Extension\TorBlock\ApiTorBlock
 */
class ApiTorBlockTest extends ApiTestCase {
	public function testValidIpNotInTorList() {
		$apiResult = $this->doApiRequestWithToken( [
			'action' => 'torblock',
			'ip' => '127.0.0.1',
		] )[0];

		$this->assertArrayHasKey( 'torblock', $apiResult );
		$this->assertSame( false, $apiResult['torblock']['result'] );
	}

	public function testInvalidIP() {
		$this->expectApiErrorCode( 'badvalue' );
		$this->doApiRequestWithToken( [
			'action' => 'torblock',
			'ip' => 'foo',
		] );
	}

}
