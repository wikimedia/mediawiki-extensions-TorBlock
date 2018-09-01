<?php
/**
 * Alias file for renamed maintenance script in version 1.30.
 * Backward compatibility for cron jobs.
 * @deprecated since 1.31; please use maintenance/loadExitNodes.php instead.
 */
require_once __DIR__ . '/maintenance/loadExitNodes.php';
