<?php

namespace MediaWiki\Extension\TorBlock\Tests;

use MediaWiki\Block\AbstractBlock;
use MediaWiki\Block\AutoblockExemptionList;
use MediaWiki\Block\DatabaseBlock;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\TorBlock\Hooks;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @group TorBlock
 * @covers \MediaWiki\Extension\TorBlock\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {

	private function newHooks(): Hooks {
		return new Hooks(
			$this->createMock( AutoblockExemptionList::class ),
			$this->createMock( HookContainer::class )
		);
	}

	/**
	 * Builds a Hooks instance + mocked User for a given tor/permission scenario.
	 *
	 * @return array{0:Hooks,1:User}
	 */
	private function makeHooks(
		string $ip,
		array $allowedActions,
		array $bypassPerms,
		bool $userIsAllowed,
		bool $isExempt
	): array {
		RequestContext::getMain()->getRequest()->setIP( $ip );
		$this->overrideConfigValue( 'TorAllowedActions', $allowedActions );
		$this->overrideConfigValue( 'TorBypassPermissions', $bypassPerms );

		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )->willReturn( $userIsAllowed );

		$exemptionList = $this->createMock( AutoblockExemptionList::class );
		$exemptionList->method( 'isExempt' )->willReturn( $isExempt );

		$hooks = new Hooks( $exemptionList, $this->createMock( HookContainer::class ) );

		return [ $hooks, $user ];
	}

	/**
	 * @dataProvider provideOnListDefinedTags
	 */
	public function testOnListDefinedTags( bool $torTagChanges, array $expected ): void {
		$this->overrideConfigValue( 'TorTagChanges', $torTagChanges );

		$hooks = $this->newHooks();
		$tags = [];
		$hooks->onListDefinedTags( $tags );

		$this->assertSame( $expected, $tags );
	}

	public static function provideOnListDefinedTags(): iterable {
		yield 'enabled' => [ true, [ 'tor' ] ];
		yield 'disabled' => [ false, [] ];
	}

	/**
	 * @dataProvider provideOnAbortAutoblock
	 */
	public function testOnAbortAutoblock( string $autoblockip, bool $expected ): void {
		$hooks = $this->newHooks();
		$block = $this->createMock( DatabaseBlock::class );

		$this->assertSame( $expected, $hooks->onAbortAutoblock( $autoblockip, $block ) );
	}

	public static function provideOnAbortAutoblock(): iterable {
		yield 'tor exit node' => [ '192.0.2.111', false ];
		yield 'non-tor ip' => [ '1.2.3.4', true ];
	}

	/**
	 * @dataProvider provideOnListDefinedTags
	 */
	public function testOnChangeTagsListActive( bool $torTagChanges, array $expected ): void {
		$this->overrideConfigValue( 'TorTagChanges', $torTagChanges );

		$hooks = $this->newHooks();
		$tags = [];
		$hooks->onChangeTagsListActive( $tags );

		$this->assertSame( $expected, $tags );
	}

	/**
	 * @dataProvider provideOnOtherBlockLogLink
	 */
	public function testOnOtherBlockLogLink( string $ip, bool $expectEntry ): void {
		$hooks = $this->newHooks();
		$msg = [];
		$hooks->onOtherBlockLogLink( $msg, $ip );

		if ( $expectEntry ) {
			$this->assertCount( 1, $msg );
			$this->assertStringContainsString( 'mw-torblock-isexitnode', $msg[0] );
		} else {
			$this->assertSame( [], $msg );
		}
	}

	public static function provideOnOtherBlockLogLink(): iterable {
		yield 'tor exit node' => [ '192.0.2.111', true ];
		yield 'non-tor ip' => [ '1.2.3.4', false ];
	}

	/**
	 * @dataProvider provideCheckUserCan
	 */
	public function testCheckUserCan(
		string $ip,
		string $action,
		array $allowedActions,
		array $bypassPerms,
		bool $userIsAllowed,
		bool $isExempt,
		bool $expected
	): void {
		[ $hooks, $user ] = $this->makeHooks( $ip, $allowedActions, $bypassPerms, $userIsAllowed, $isExempt );

		$result = TestingAccessWrapper::newFromObject( $hooks )->checkUserCan( $user, $action );

		$this->assertSame( $expected, $result );
	}

	public static function provideCheckUserCan(): iterable {
		yield 'whitelisted action' => [
			'192.0.2.111', 'read', [ 'read' ], [], false, false, true
		];
		yield 'not an exit node' => [
			'1.2.3.4', 'edit', [], [], false, false, true
		];
		yield 'exit node, has bypass perm' => [
			'192.0.2.111', 'edit', [], [ 'torunblocked' ], true, false, true
		];
		yield 'exit node, exempt IP' => [
			'192.0.2.111', 'edit', [], [ 'torunblocked' ], false, true, true
		];
		yield 'exit node, blocked' => [
			'192.0.2.111', 'edit', [], [ 'torunblocked' ], false, false, false
		];
	}

	/**
	 * @dataProvider provideOnGetUserPermissionsErrorsExpensive
	 */
	public function testOnGetUserPermissionsErrorsExpensive(
		string $ip,
		string $action,
		array $allowedActions,
		array $bypassPerms,
		bool $userIsAllowed,
		bool $isExempt,
		bool $expectedReturn,
		array $expectedResult
	): void {
		[ $hooks, $user ] = $this->makeHooks( $ip, $allowedActions, $bypassPerms, $userIsAllowed, $isExempt );

		$result = [];
		$return = $hooks->onGetUserPermissionsErrorsExpensive( null, $user, $action, $result );

		$this->assertSame( $expectedReturn, $return );
		$this->assertSame( $expectedResult, $result );
	}

	public static function provideOnGetUserPermissionsErrorsExpensive(): iterable {
		yield 'allowed' => [
			'1.2.3.4', 'edit', [], [], false, false, true, []
		];
		yield 'blocked' => [
			'192.0.2.111', 'edit', [], [ 'torunblocked' ], false, false, false,
			[ 'torblock-blocked', '192.0.2.111' ]
		];
	}

	/**
	 * @dataProvider provideOnUserCanSendEmail
	 */
	public function testOnUserCanSendEmail(
		string $ip,
		bool $expectedReturn,
		array $expectedHookErr
	): void {
		[ $hooks, $user ] = $this->makeHooks( $ip, [], [], false, false );

		$hookErr = [];
		$return = $hooks->onUserCanSendEmail( $user, $hookErr );

		$this->assertSame( $expectedReturn, $return );
		$this->assertSame( $expectedHookErr, $hookErr );
	}

	public static function provideOnUserCanSendEmail(): iterable {
		yield 'allowed' => [ '1.2.3.4', true, [] ];
		yield 'blocked' => [
			'192.0.2.111', false,
			[ 'permissionserrors', 'torblock-blocked', [ '192.0.2.111' ] ]
		];
	}

	/**
	 * @dataProvider provideOnRecentChangeSave
	 */
	public function testOnRecentChange_save( bool $torTagChanges, string $ip, int $expectedCalls ): void {
		$this->overrideConfigValue( 'TorTagChanges', $torTagChanges );
		RequestContext::getMain()->getRequest()->setIP( $ip );

		$hooks = $this->newHooks();

		$rc = $this->createMock( RecentChange::class );
		$rc->expects( $this->exactly( $expectedCalls ) )->method( 'addTags' )->with( 'tor' );

		$hooks->onRecentChange_save( $rc );
	}

	public static function provideOnRecentChangeSave(): iterable {
		yield 'enabled, tor ip' => [ true, '192.0.2.111', 1 ];
		yield 'enabled, non-tor ip' => [ true, '1.2.3.4', 0 ];
		yield 'disabled' => [ false, '192.0.2.111', 0 ];
	}

	/**
	 * @dataProvider provideOnGetAutoPromoteGroups
	 */
	public function testOnGetAutoPromoteGroups(
		array $initialPromote,
		string $registration,
		int $editCount,
		string $ip,
		array $expectedPromote
	): void {
		$this->overrideConfigValue( 'TorAutoConfirmAge', 86400 );
		$this->overrideConfigValue( 'TorAutoConfirmCount', 10 );
		RequestContext::getMain()->getRequest()->setIP( $ip );

		$user = $this->createMock( User::class );
		$user->method( 'getRegistration' )->willReturn( $registration );
		$user->method( 'getEditCount' )->willReturn( $editCount );

		$hooks = $this->newHooks();

		$promote = $initialPromote;
		$hooks->onGetAutoPromoteGroups( $user, $promote );

		$this->assertSame( $expectedPromote, $promote );
	}

	public static function provideOnGetAutoPromoteGroups(): iterable {
		yield 'empty promote' => [
			[], '20010101000000', 500, '192.0.2.111', []
		];
		yield 'meets requirements' => [
			[ 'autoconfirmed' ], '20010101000000', 500, '192.0.2.111', [ 'autoconfirmed' ]
		];
		yield 'tor user below requirements' => [
			[ 'autoconfirmed' ], '20990101000000', 0, '192.0.2.111', []
		];
		yield 'non-tor below requirements' => [
			[ 'autoconfirmed' ], '20990101000000', 0, '1.2.3.4', [ 'autoconfirmed' ]
		];
	}

	/**
	 * @dataProvider provideOnUserRequirementsCondition
	 */
	public function testOnUserRequirementsCondition(
		string|int $condition,
		string|false $wikiId,
		bool $isPerformer,
		string $ip,
		?bool $expectedResult
	): void {
		RequestContext::getMain()->getRequest()->setIP( $ip );

		$user = $this->createMock( UserIdentity::class );
		$user->method( 'getWikiId' )->willReturn( $wikiId );

		$hooks = $this->newHooks();

		$result = null;
		$hooks->onUserRequirementsCondition( $condition, [], $user, $isPerformer, $result );

		$this->assertSame( $expectedResult, $result );
	}

	public static function provideOnUserRequirementsCondition(): iterable {
		yield 'other condition' => [
			'other-condition', UserIdentity::LOCAL, true, '192.0.2.111', null
		];
		yield 'non-local' => [
			APCOND_TOR, 'otherwiki', true, '192.0.2.111', false
		];
		yield 'not performing' => [
			APCOND_TOR, UserIdentity::LOCAL, false, '192.0.2.111', false
		];
		yield 'local, performing, tor' => [
			APCOND_TOR, UserIdentity::LOCAL, true, '192.0.2.111', true
		];
		yield 'local, performing, non-tor' => [
			APCOND_TOR, UserIdentity::LOCAL, true, '1.2.3.4', false
		];
	}

	/**
	 * @dataProvider provideOnGetUserBlock
	 */
	public function testOnGetUserBlock(
		?int $blockType,
		bool $disable,
		string $ip,
		bool $expectNull
	): void {
		if ( $blockType === null ) {
			$block = null;
		} else {
			$single = $this->createMock( DatabaseBlock::class );
			$single->method( 'getType' )->willReturn( $blockType );
			$block = $this->createMock( AbstractBlock::class );
			$block->method( 'toArray' )->willReturn( [ $single ] );
		}

		$this->overrideConfigValue( 'TorDisableAdminBlocks', $disable );
		RequestContext::getMain()->getRequest()->setIP( $ip );

		$hooks = $this->newHooks();
		$hooks->onGetUserBlock( null, '', $block );

		if ( $expectNull ) {
			$this->assertNull( $block );
		} else {
			$this->assertNotNull( $block );
		}
	}

	public static function provideOnGetUserBlock(): iterable {
		yield 'no block' => [
			null, true, '192.0.2.111', true
		];
		yield 'admin blocks not disabled' => [
			AbstractBlock::TYPE_IP, false, '192.0.2.111', false
		];
		yield 'not a tor ip' => [
			AbstractBlock::TYPE_IP, true, '1.2.3.4', false
		];
		yield 'block includes a user block' => [
			AbstractBlock::TYPE_USER, true, '192.0.2.111', false
		];
		yield 'ip-only block on tor node' => [
			AbstractBlock::TYPE_IP, true, '192.0.2.111', true
		];
	}

}
