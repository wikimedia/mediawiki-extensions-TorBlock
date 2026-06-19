<?php

namespace MediaWiki\Extension\TorBlock\Tests;

use MediaWiki\Extension\TorBlock\TorExitNodes;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @group TorBlock
 * @covers \MediaWiki\Extension\TorBlock\TorExitNodes
 */
class TorExitNodesTest extends MediaWikiIntegrationTestCase {
	use \MockHttpTrait;

	public function testFetchExitNodesFromOnionooServerReturnsEmptyArrayWhenNoRelays(): void {
		$json = json_encode( [
			'relays' => []
		] );

		$this->installMockHttp( $json );

		$torExitNodes = TestingAccessWrapper::newFromClass( TorExitNodes::class );

		$this->assertSame(
			[],
			$torExitNodes->fetchExitNodesFromOnionooServer()
		);
	}

	public function testFetchExitNodesFromOnionooServerReturnsSingleExitNode(): void {
		$json = json_encode( [
			'relays' => [
				[
					'or_addresses' => [ '1.2.3.4:9001' ]
				]
			]
		] );

		$this->installMockHttp( $json );

		$torExitNodes = TestingAccessWrapper::newFromClass( TorExitNodes::class );

		$this->assertSame(
			[ '1.2.3.4' ],
			$torExitNodes->fetchExitNodesFromOnionooServer()
		);
	}

	public function testFetchExitNodesFromOnionooServerFlattensMultipleRelays(): void {
		$json = json_encode( [
			'relays' => [
				[
					'or_addresses'   => [ '1.2.3.4:9001' ],
					'exit_addresses' => [ '1.2.3.5' ]
				],
				[
					'or_addresses' => [ '5.6.7.8:443' ]
				]
			]
		] );

		$this->installMockHttp( $json );

		$torExitNodes = TestingAccessWrapper::newFromClass( TorExitNodes::class );

		$this->assertSame(
			[ '1.2.3.4', '1.2.3.5', '5.6.7.8' ],
			$torExitNodes->fetchExitNodesFromOnionooServer()
		);
	}

	public function testFetchExitNodesFromOnionooServerIncludesIpv6Addresses(): void {
		$json = json_encode( [
			'relays' => [
				[
					'or_addresses' => [ '[2001:db8::1]:443', '1.2.3.4:9001' ]
				]
			]
		] );

		$this->installMockHttp( $json );

		$torExitNodes = TestingAccessWrapper::newFromClass( TorExitNodes::class );

		$result = $torExitNodes->fetchExitNodesFromOnionooServer();

		$this->assertContains( '1.2.3.4', $result );
		$this->assertContains( '2001:DB8:0:0:0:0:0:1', $result );
	}

	public function testFetchExitNodesFromOnionooServerSkipsInvalidIps(): void {
		$json = json_encode( [
			'relays' => [
				[ 'or_addresses' => [ '999.999.0.1:9001', '1.2.3.4:9001' ] ]
			]
		] );

		$this->installMockHttp( $json );

		$torExitNodes = TestingAccessWrapper::newFromClass( TorExitNodes::class );
		$result = $torExitNodes->fetchExitNodesFromOnionooServer();

		$this->assertContains( '1.2.3.4', $result );
		$this->assertNotContains( '999.999.0.1', $result );
	}
}
