<?php

/**
 * Hooks for the Extension:TorBlock for MediaWiki
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

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// Need to be able to define ::onRecentChange_Save

namespace MediaWiki\Extension\TorBlock;

use MediaWiki\Block\AbstractBlock;
use MediaWiki\Block\DatabaseBlock;
use MediaWiki\Block\Hook\AbortAutoblockHook;
use MediaWiki\Block\Hook\GetUserBlockHook;
use MediaWiki\ChangeTags\Hook\ChangeTagsListActiveHook;
use MediaWiki\ChangeTags\Hook\ListDefinedTagsHook;
use MediaWiki\Extension\TorBlock\Hooks\HookRunner;
use MediaWiki\Hook\OtherBlockLogLinkHook;
use MediaWiki\Hook\RecentChange_saveHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsExpensiveHook;
use MediaWiki\Title\Title;
use MediaWiki\User\Hook\AutopromoteConditionHook;
use MediaWiki\User\Hook\GetAutoPromoteGroupsHook;
use MediaWiki\User\Hook\UserCanSendEmailHook;
use MediaWiki\User\User;
use RecentChange;
use Wikimedia\IPUtils;

class Hooks implements
	AbortAutoblockHook,
	AutopromoteConditionHook,
	GetUserPermissionsErrorsExpensiveHook,
	GetAutoPromoteGroupsHook,
	GetUserBlockHook,
	RecentChange_saveHook,
	ListDefinedTagsHook,
	ChangeTagsListActiveHook,
	UserCanSendEmailHook,
	OtherBlockLogLinkHook
{

	private HookRunner $hookRunner;

	public static function registerExtension() {
		// Define new autopromote condition
		// Numbers won't work, we'll get collisions
		define( 'APCOND_TOR', 'tor' );
	}

	public function __construct( HookContainer $hookContainer ) {
		$this->hookRunner = new HookRunner( $hookContainer );
	}

	/**
	 * Whether the given user is allowed to perform $action from its current IP
	 *
	 * @param User $user
	 * @param string|null $action
	 * @return bool
	 */
	private static function checkUserCan( User $user, $action = null ) {
		global $wgTorAllowedActions, $wgRequest;

		if ( ( $action !== null && in_array( $action, $wgTorAllowedActions ) )
			|| !TorExitNodes::isExitNode()
		) {
			return true;
		}

		wfDebugLog( 'torblock', "User detected as editing through tor." );

		global $wgTorBypassPermissions;
		foreach ( $wgTorBypassPermissions as $perm ) {
			if ( $user->isAllowed( $perm ) ) {
				wfDebugLog( 'torblock', "User has $perm permission. Exempting from Tor Blocks." );

				return true;
			}
		}

		$ip = $wgRequest->getIP();
		if ( DatabaseBlock::isExemptedFromAutoblocks( $ip ) ) {
			wfDebugLog( 'torblock', "IP is excluded from autoblocks. Exempting from Tor Blocks." );

			return true;
		}

		return false;
	}

	/**
	 * Check if a user is a Tor node and not excluded from autoblocks or allowed
	 * to bypass tor blocks.
	 *
	 * @param Title $title Title being acted upon
	 * @param User $user User performing the action
	 * @param string $action Action being performed
	 * @param array &$result Will be filled with block status if blocked
	 * @return bool
	 */
	public function onGetUserPermissionsErrorsExpensive(
		$title,
		$user,
		$action,
		&$result
	) {
		global $wgRequest;
		if ( !self::checkUserCan( $user, $action ) ) {
			wfDebugLog( 'torblock', "User detected as editing from Tor node. " .
				"Adding Tor block to permissions errors." );

			// Allow site customization of blocked message.
			$blockedMsg = 'torblock-blocked';
			$this->hookRunner->onTorBlockBlockedMsg( $blockedMsg );
			$result = [ $blockedMsg, $wgRequest->getIP() ];

			return false;
		}

		return true;
	}

	/**
	 * Check if the user is logged in from a Tor exit node but is not exempt.
	 * If so, block the user.
	 *
	 * @param User $user
	 * @param array &$hookErr
	 * @return bool
	 */
	public function onUserCanSendEmail( $user, &$hookErr ) {
		global $wgRequest;
		if ( !self::checkUserCan( $user ) ) {
			wfDebugLog( 'torblock', "User detected as trying to send an email from Tor node. Preventing." );

			// Allow site customization of blocked message.
			$blockedMsg = 'torblock-blocked';
			$this->hookRunner->onTorBlockBlockedMsg( $blockedMsg );
			$hookErr = [
				'permissionserrors',
				$blockedMsg,
				[ $wgRequest->getIP() ],
			];
			return false;
		}

		return true;
	}

	/**
	 * Remove a block if it only targets a Tor node. A composite block comprises
	 * multiple blocks, and if any of these target the user, then do not remove the
	 * block.
	 *
	 * @param User $user
	 * @param string|null $ip
	 * @param AbstractBlock|null &$block
	 * @return bool
	 */
	public function onGetUserBlock( $user, $ip, &$block ) {
		global $wgTorDisableAdminBlocks;
		if ( !$block || !$wgTorDisableAdminBlocks || !TorExitNodes::isExitNode() ) {
			return true;
		}

		$blocks = $block->toArray();

		$removeBlock = true;
		foreach ( $blocks as $singleBlock ) {
			if ( $singleBlock->getType() === AbstractBlock::TYPE_USER ) {
				$removeBlock = false;
				break;
			}
		}

		if ( $removeBlock ) {
			wfDebugLog( 'torblock', "User using Tor node. Disabling IP block as it was " .
				"probably targeted at the Tor node." );
			// Node is probably blocked for being a Tor node. Remove block.
			$block = null;
		}

		return true;
	}

	/**
	 * If an IP address is an exit node, stop it from being autoblocked.
	 *
	 * @param string $autoblockip IP address being blocked
	 * @param DatabaseBlock $block Block being applied
	 * @return bool
	 */
	public function onAbortAutoblock( $autoblockip, $block ) {
		return !TorExitNodes::isExitNode( $autoblockip );
	}

	/**
	 * When the user is a Tor exit node, make sure they meet configured
	 * age/edit count requirements before allowing promotions.
	 *
	 * @param User $user User being promoted
	 * @param array &$promote Groups being added
	 * @return bool
	 */
	public function onGetAutoPromoteGroups( $user, &$promote ) {
		global $wgTorAutoConfirmAge, $wgTorAutoConfirmCount;

		// Check against stricter requirements for tor nodes.
		// Counterintuitively, we do the requirement checks first.
		// This is so that we don't have to hit memcached to get the
		// exit list, unnecessarily.

		if ( !count( $promote ) ) {
			// No groups to promote to anyway
			return true;
		}

		$age = time() - (int)wfTimestampOrNull( TS_UNIX, $user->getRegistration() );

		if ( $age >= $wgTorAutoConfirmAge && $user->getEditCount() >= $wgTorAutoConfirmCount ) {
			// Does match requirements. Don't bother checking if we're an exit node.
			return true;
		}

		if ( TorExitNodes::isExitNode() ) {
			// Tor user, doesn't match the expanded requirements.
			$promote = [];
		}

		return true;
	}

	/**
	 * Check if a user is a Tor node if the wiki is configured
	 * to autopromote on Tor status.
	 *
	 * @param string $type Condition being checked
	 * @param array $args Arguments passed to the condition
	 * @param User $user User being promoted
	 * @param array &$result Will be filled with result of condition
	 * @return bool
	 */
	public function onAutopromoteCondition( $type, $args, $user, &$result ) {
		if ( $type == APCOND_TOR ) {
			$result = TorExitNodes::isExitNode();
		}

		return true;
	}

	/**
	 * If enabled, tag recent changes made by a Tor exit node.
	 *
	 * @param RecentChange $recentChange The change being saved
	 * @return bool true
	 */
	public function onRecentChange_Save( $recentChange ) {
		global $wgTorTagChanges;

		if ( $wgTorTagChanges && TorExitNodes::isExitNode() ) {
			$recentChange->addTags( 'tor' );
		}
		return true;
	}

	/**
	 * If enabled, add a new tag type for recent changes made by Tor exit nodes.
	 *
	 * @param array &$emptyTags List of defined tags (for ListDefinedTags hook) or
	 * list of active tags (for ChangeTagsListActive hook)
	 * @return bool true
	 */
	public function onListDefinedTags( &$emptyTags ) {
		global $wgTorTagChanges;

		if ( $wgTorTagChanges ) {
			$emptyTags[] = 'tor';
		}
		return true;
	}

	/**
	 * @param string[] &$tags
	 *
	 * @return bool
	 */
	public function onChangeTagsListActive( &$tags ) {
		return $this->onListDefinedTags( $tags );
	}

	/**
	 * Creates a message with the Tor blocking status if applicable.
	 *
	 * @param array &$msg Message with the status
	 * @param string $ip The IP address to be checked
	 * @return bool true
	 */
	public function onOtherBlockLogLink( &$msg, $ip ) {
		// IP addresses can be blocked only
		// Fast return if IP is not an exit node
		if ( IPUtils::isIPAddress( $ip ) && TorExitNodes::isExitNode( $ip ) ) {
			$msg[] = Html::rawElement(
				'span',
				[ 'class' => 'mw-torblock-isexitnode' ],
				wfMessage( 'torblock-isexitnode', $ip )->parse()
			);
		}
		return true;
	}
}
