<?php
/**
 * Updates the tor exit node list in
 */

require_once ( getenv('MW_INSTALL_PATH') !== false
	? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
	: __DIR__ . '/../../maintenance/commandLine.inc' );

TorBlock::loadExitNodes();
