<?php

namespace zsql\Tests\Fixtures\LoaderA;

use zsql\Migrator\LegacyMigration;

class Migration1234_TestA extends LegacyMigration
{
    public function __construct()
    {
        throw new \Exception('testing exception in constructor');
    }
    
    public function up()
    {
        
    }
    
    public function down()
    {
        
    }
}