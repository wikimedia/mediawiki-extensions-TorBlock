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
	private HookContainer $hookContainer;

	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
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
