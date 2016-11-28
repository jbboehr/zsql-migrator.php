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

Loader::_()->migration()
    ->version(1412292426)
    ->name("TestC")
    ->up(function(Adapter $database) {
        $database->query('create table `migrationtestc` ( `test` int );');
    })
    ->down(function(Adapter $database) {
        $database->query('drop table `migrationtestc`');
    })
    ->save();

Loader::_()->migration()
    ->version(1412292439)
    ->name("TestD")
    ->up(function(Adapter $database) {
        $database->query('create table `migrationtestd` ( `test` int );');
    })
    ->down(function(Adapter $database) {
        $database->query('drop table `migrationtestd`');
    })
    ->save();

Loader::_()->migration()
    ->version(1412292453)
    ->name("TestE")
    ->up(function(Adapter $database) {
        $database->query('create table `migrationteste` ( `test` int );');
    })
    ->down(function(Adapter $database) {
        $database->query('drop table `migrationteste`');
    })
    ->save();
