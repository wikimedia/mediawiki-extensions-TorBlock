<?php

/**
 * Updates the tor exit node list in
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
 */

require_once( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . "/maintenance/Maintenance.php"
	: __DIR__ . '/../../maintenance/Maintenance.php' );

/**
 * Maintenance script to load/reload the list of Tor exit nodes.
 *
 * @ingroup Maintenance
 * @ingroup Extensions
 */
class LoadExitNodes extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Load the list of Tor exit nodes.";
		$this->addOption( 'force', 'Force loading of exit nodes from the server rather than cache.' );
	}

	public function execute() {
		if ( $this->getOption( 'force', false ) ) {
			TorExitNodes::loadExitNodes();
		}
		$nodes = TorExitNodes::getExitNodes();
		if ( !$nodes ) {
			$this->error( "Could not load exit nodes.", true );
		}
		$this->output( 'Successfully loaded ' . count( $nodes ) . " exit nodes.\n" );
	}
}

$maintClass = 'LoadExitNodes';
require_once( RUN_MAINTENANCE_IF_MAIN );
