<?php

namespace zsql\Tests\Fixtures\MigrationsD;

use zsql\Migrator\LegacyMigration;

abstract class MyBaseMigration extends LegacyMigration {}

class Migration1412129062_TestA extends MyBaseMigration
{
    public function up()
    {
        $this->database->query('create table `migrationtesta` ( `test` int );');
    }
    
    public function down()
    {
        $this->database->query('drop table `migrationtesta`');
    }
}

class Migration1412129177_TestB extends MyBaseMigration
{
    public function up()
    {
        $this->database->query('create table `migrationtestb` ( `test` int );');
    }
    
    public function down()
    {
        $this->database->query('drop table `migrationtestb`');
    }
}
