<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace MediaWiki\Extension\TorBlock;

use MediaWiki\Maintenance\Benchmarker;
use Wikimedia\IPSet;
use Wikimedia\RunningStat;
use const RUN_MAINTENANCE_IF_MAIN;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/includes/Benchmarker.php";

/**
 * Benchmark TorBlock
 *
 * @codeCoverageIgnore
 */
class Benchmark extends Benchmarker {
	public function __construct() {
		$this->defaultCount = 10;
		parent::__construct();
		$this->addDescription( 'Benchmark for TorBlock' );
		$this->requireExtension( 'TorBlock' );
	}

	public function execute() {
		$nodes = TorExitNodes::loadExitNodes();

		$stat = new RunningStat();
		$t = microtime( true );
		$set = new IPSet( $nodes );
		$t = ( microtime( true ) - $t ) * 1000;

		$stat->addObservation( $t );

		// Copy pasta from Benchmarker.php
		$this->addResult( [
			'name' => 'setup IPSet',
			'count' => $stat->getCount(),
			// Get rate per second from mean (in ms)
			'rate' => $stat->getMean() == 0 ? INF : ( 1.0 / ( $stat->getMean() / 1000.0 ) ),
			'total' => $stat->getMean() * $stat->getCount(),
			'mean' => $stat->getMean(),
			'max' => $stat->max,
			'stddev' => $stat->getStdDev(),
			'usage' => [
				'mem' => memory_get_usage( true ),
				'mempeak' => memory_get_peak_usage( true ),
			],
		] );

		$benches = [
			[
				'function' => [ TorExitNodes::class, 'isExitNode' ],
				'args' => [ '127.0.0.1' ],
			],
			// Comparing in_array against IPSet
			[
				'function' => 'in_array',
				'args' => [ '127.0.0.1', $nodes ],
			],
			[
				'function' => [ $set, 'match' ],
				'args' => [ '127.0.0.1' ],
			],
		];
		$this->bench( $benches );
	}
}

$maintClass = Benchmark::class;
require_once RUN_MAINTENANCE_IF_MAIN;
