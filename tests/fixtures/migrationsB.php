<?php

use zsql\Adapter;
use zsql\Migrator\Loader;

Loader::_()->migration()
    ->version(1412129062)
    ->name("TestA")
    ->up(function(Adapter $database) {
        $database->query('create table `migrationtesta` ( `test` int );');
    })
    ->down(function(Adapter $database) {
        $database->query('drop table `migrationtesta`');
    })
    ->save();

Loader::_()->migration()
    ->version(1412129177)
    ->name("TestB")
    ->up(function(Adapter $database) {
        $database->query('create table `migrationtestb` ( `test` int );');
    })
    ->down(function(Adapter $database) {
        $database->query('drop table `migrationtestb`');
    })
    ->save();
