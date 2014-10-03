<?php

use zsql\Migrator\Command;

if( file_exists($file = __DIR__ . '/../vendor/autoload.php') ) {
    require_once $file;
} else if( file_exists($file = __DIR__ . '/../../../vendor/autoload.php') ) {
    require_once $file;
}

$args = array_slice($argv, 1);
$command = new Command(array(), $args);
$command->run();
