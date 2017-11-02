#!/usr/bin/php
<?php
ignore_user_abort(1);
set_time_limit(0);
date_default_timezone_set('UTC');

require_once ("vendor/autoload.php");

use kozintsev\ALogger\Logger;

$logger = new Logger(__DIR__ . '/tests/logs/test.log', \Psr\Log\LogLevel::DEBUG);
$logger->info('test');