<?php

namespace MediaWiki\Extension\TorBlock\Tests;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Extension\TorBlock\TorBlockAbuseFilterHooks;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * @group TorBlock
 * @covers \MediaWiki\Extension\TorBlock\TorBlockAbuseFilterHooks
 */
class TorBlockAbuseFilterHooksTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		// The class implements AbuseFilter interfaces, which are only present when it is loaded.
		$this->markTestSkippedIfExtensionNotLoaded( 'Abuse Filter' );
	}

	/**
	 * @dataProvider provideOnAbuseFilterAlterVariables
	 */
	public function testOnAbuseFilterAlterVariables( string $ip, bool $expected ): void {
		RequestContext::getMain()->getRequest()->setIP( $ip );

		$vars = new VariableHolder();
		$hooks = new TorBlockAbuseFilterHooks();
		$hooks->onAbuseFilterAlterVariables(
			$vars,
			Title::makeTitle( NS_MAIN, 'Test page' ),
			$this->createMock( User::class )
		);

		$this->assertSame( $expected, $vars->getVars()['tor_exit_node']->toNative() );
	}

	public static function provideOnAbuseFilterAlterVariables(): iterable {
		// fetchExitNodes() returns a fixed list under MW_PHPUNIT_TEST.
		yield 'tor exit node' => [ '192.0.2.111', true ];
		yield 'non-tor ip' => [ '1.2.3.4', false ];
	}

	public function testOnAbuseFilterBuilder(): void {
		$realValues = [];
		$hooks = new TorBlockAbuseFilterHooks();
		$hooks->onAbuseFilter_builder( $realValues );

		$this->assertSame( [ 'vars' => [ 'tor_exit_node' => 'tor-exit-node' ] ], $realValues );
	}
}
