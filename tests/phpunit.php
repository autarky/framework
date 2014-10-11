<?php
define('TESTS_RSC_DIR', __DIR__.'/resources');

$loader = require dirname(__DIR__) . '/vendor/autoload.php';

Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

date_default_timezone_set('UTC');
