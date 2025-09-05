<?php

namespace MediaWiki\Extension\TorBlock\Hooks;

use MediaWiki\HookContainer\HookContainer;

/**
 * This is a hook runner class, see docs/Hooks.md in core.
 * @internal
 */
class HookRunner implements
	TorBlockBlockedMsgHook
{
	public function __construct( private readonly HookContainer $hookContainer ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onTorBlockBlockedMsg( string &$blockedMsg ) {
		return $this->hookContainer->run(
			'TorBlockBlockedMsg',
			[ &$blockedMsg ]
		);
	}
}
