# ALogger is fork [KLogger](https://github.com/katzgrau/KLogger) and simple logging for PHP

A project forked and refactored Oleg Kozinsev.

## About

All that relates to Klogger is also relevant for ALogger. 
But Alogger creates an instance of the object for saving every time it is saved, which allows you to delete the log file while you work.
Also removed all options and added the ability to split the file into parts.

### Composer

From the Command Line:

```
composer require kozintsev/a-logger:dev-master
```

In your `composer.json`:

``` json
{
    "require": {
        "kozintsev/a-logger": "dev-master"
    }
}
```

## Basic Usage

``` php
<?php

require_once ("vendor/autoload.php");

use kozintsev\ALogger\Logger;

$logger = new Logger(__DIR__ . '/tests/logs/test.log', \Psr\Log\LogLevel::DEBUG);
$logger->info('Returned a million search results');
$logger->error('Oh dear.');
$logger->debug('Got these users from the Database.', $users);
```

### Output

```
[2014-03-20 3:35:43.762437] [INFO] Returned a million search results
[2014-03-20 3:35:43.762578] [ERROR] Oh dear.
[2014-03-20 3:35:43.762795] [DEBUG] Got these users from the Database.
    0: array(
        'name' => 'Kenny Katzgrau',
        'username' => 'katzgrau',
    )
    1: array(
        'name' => 'Dan Horrigan',
        'username' => 'dhrrgn',
    )
```

### Additional Options

ALogger supports additional options via third parameter in the constructor:

``` php
<?php
// Example
$logger = new kozintsev\ALogger\Logger(__DIR__ . '/tests/logs/test.log', Psr\Log\LogLevel::WARNING, [
    'max_file_size' => 0, // changes the log file extension
]);
```

Here's the full list:

| Option | Default | Description |
| ------ | ------- | ----------- |
| max_file_size | 5120000 |the maximum size of the log file, if set to 0, the size is not checked  |
