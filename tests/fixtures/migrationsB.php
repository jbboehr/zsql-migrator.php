<?php

use zsql\Database;
use zsql\Migrator\Loader;

Loader::_()->migration()
    ->version(1412129062)
    ->name("TestA")
    ->up(function(Database $database) {
        $database->query('create table `migrationtesta` ( `test` int );');
    })
    ->down(function(Database $database) {
        $database->query('drop table `migrationtesta`');
    })
    ->save();
    
Loader::_()->migration()
    ->version(1412129177)
    ->name("TestB")
    ->up(function(Database $database) {
        $database->query('create table `migrationtestb` ( `test` int );');
    })
    ->down(function(Database $database) {
        $database->query('drop table `migrationtestb`');
    })
    ->save();
