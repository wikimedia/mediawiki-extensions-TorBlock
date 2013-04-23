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
 * @link http://www.mediawiki.org/wiki/Extension:TorBlock Documentation
 *
 * @author Andrew Garrett <andrew@epstone.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$dir = __DIR__;
$wgExtensionCredits['antispam'][] = array(
	'path'           => __FILE__,
	'name'           => 'TorBlock',
	'author'         => 'Andrew Garrett',
	'descriptionmsg' => 'torblock-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:TorBlock',
);

$wgExtensionMessagesFiles['TorBlock'] =  "$dir/TorBlock.i18n.php";
$wgAutoloadClasses['TorBlock'] = "$dir/TorBlock.class.php";
$wgAutoloadClasses['TorExitNodes'] = "$dir/TorExitNodes.php";

$wgHooks['getUserPermissionsErrorsExpensive'][] = 'TorBlock::onGetUserPermissionsErrorsExpensive';
$wgHooks['AbortAutoblock'][] = 'TorBlock::onAbortAutoblock';
$wgHooks['GetAutoPromoteGroups'][] = 'TorBlock::onGetAutoPromoteGroups';
$wgHooks['GetBlockedStatus'][] = 'TorBlock::onGetBlockedStatus';
$wgHooks['AutopromoteCondition'][] = 'TorBlock::onAutopromoteCondition';
$wgHooks['RecentChange_save'][] = 'TorBlock::onRecentChangeSave';
$wgHooks['ListDefinedTags'][] = 'TorBlock::onListDefinedTags';
$wgHooks['AbuseFilter-filterAction'][] = 'TorBlock::onAbuseFilterFilterAction';
$wgHooks['AbuseFilter-builder'][] = 'TorBlock::onAbuseFilterBuilder';
$wgHooks['EmailUserPermissionsErrors'][] = 'TorBlock::onEmailUserPermissionsErrors';
$wgHooks['OtherBlockLogLink'][] = 'TorBlock::getTorBlockStatus';

// Define new autopromote condition
define( 'APCOND_TOR', 'tor' ); // Numbers won't work, we'll get collisions

/**
 * Permission keys that bypass Tor blocks.
 * Array of permission keys.
 */
$wgTorBypassPermissions = array( 'torunblocked', /*'autoconfirmed', 'proxyunbannable'*/ );
$wgAvailableRights[] = 'torunblocked';

$wgGroupPermissions['user']['torunblocked'] = true;

/**
 * Whether to load Tor blocks if they aren't stored in memcached.
 * Set to false on high-load sites, and use a cron job with the included
 * maintenance script
 */
$wgTorLoadNodes = true;

/**
 * Actions tor users are allowed to do.
 * E.g. to allow account creation, add createaccount.
 */
$wgTorAllowedActions = array( 'read' );

/**
 * Autoconfirm limits for tor users.
 * Both regular limits, AND Tor limits must be passed.
 */
$wgTorAutoConfirmAge = 0;
$wgTorAutoConfirmCount = 0;

/**
 * IPs to check for tor exits to.
 * (i.e. all IPs which can be used to access the site.
 */
$wgTorIPs = array( '208.80.152.2' );

/**
 * Onionoo server to use to poll information from for exit nodes.
 */
$wgTorOnionooServer = 'https://onionoo.torproject.org';

/**
 * Path to the CA file for the Onionoo server.
 * Set to false or any other invalid value to disable.
 */
$wgTorOnionooCA = "$dir/torproject.crt";

/**
 * Path to the CA file for the Tor Project.
 * Set to false or any other invalid value to disable.
 */
$wgTorProjectCA = "$dir/torproject.crt";

/**
 * Disable existing blocks of Tor nodes
 */
$wgTorDisableAdminBlocks = true;

/** Mark tor edits as such */
$wgTorTagChanges = true;

/**
 * Proxy to use, if not the default proxy
 */
$wgTorBlockProxy = false;