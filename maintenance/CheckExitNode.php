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
 * @ingroup Extensions
 */

namespace MediaWiki\Extension\TorBlock\Maintenance;

use MediaWiki\Extension\TorBlock\TorExitNodes;
use MediaWiki\Maintenance\Maintenance;
use Wikimedia\IPUtils;

// @codeCoverageIgnoreStart
require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . "/maintenance/Maintenance.php"
	: __DIR__ . '/../../../maintenance/Maintenance.php';
// @codeCoverageIgnoreEnd

/**
 * @ingroup Maintenance
 * @ingroup Extensions
 *
 * @codeCoverageIgnore
 */
class CheckExitNode extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Check if an IP is a Tor exit node." );
		$this->addArg( 'ip', 'IP to check if it is a Tor exit node' );
		$this->requireExtension( "TorBlock" );
	}

	public function execute() {
		$ip = $this->getArg( 0 );
		if ( !IPUtils::isIPAddress( $ip ) ) {
			$this->fatalError( "$ip is not an IP Address.\n" );
		}

		if ( TorExitNodes::isExitNode( $ip ) ) {
			$this->output( "$ip is a Tor exit node!\n" );
		} else {
			$this->output( "$ip is not a Tor exit node!\n" );
		}
	}
}

// @codeCoverageIgnoreStart
$maintClass = CheckExitNode::class;
require_once RUN_MAINTENANCE_IF_MAIN;
// @codeCoverageIgnoreEnd
