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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @license GPL-2.0-or-later
 */

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// For onAbuseFilter_builder

namespace MediaWiki\Extension\TorBlock;

use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterAlterVariablesHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class TorBlockAbuseFilterHooks implements AbuseFilterAlterVariablesHook, AbuseFilterBuilderHook {
	/**
	 * @inheritDoc
	 */
	public function onAbuseFilterAlterVariables( VariableHolder &$vars, Title $title, User $user ) {
		$vars->setVar( 'tor_exit_node', TorExitNodes::isExitNode() );
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function onAbuseFilter_builder( array &$realValues ) {
		$realValues['vars']['tor_exit_node'] = 'tor-exit-node';
		return true;
	}
}
