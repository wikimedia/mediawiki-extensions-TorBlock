<?php

namespace MediaWiki\Extension\TorBlock\Hooks;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TorBlockBlockedMsg" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface TorBlockBlockedMsgHook {
	/**
	 * @param string &$blockedMsg
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTorBlockBlockedMsg( string &$blockedMsg );
}
