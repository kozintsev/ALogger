#!/usr/bin/php
<?php
ignore_user_abort(1);
set_time_limit(0);
date_default_timezone_set('UTC');

require_once ("vendor/autoload.php");

use kozintsev\ALogger\Logger;

$logger = new Logger(__DIR__ . '/tests/logs/test.log', \Psr\Log\LogLevel::DEBUG, [
    'max_file_size' => 0, // max file size, if set to 0, the size is not checked
]);
$logger->info('test');

