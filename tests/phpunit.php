<?php
if (!defined('TESTS_RSC_DIR')) {
	define('TESTS_RSC_DIR', __DIR__.'/resources');
}

$loader = require_once dirname(__DIR__) . '/vendor/autoload.php';

Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

date_default_timezone_set('UTC');
