<?php

use zsql\Database;
use zsql\Migrator\FluentMigration;

class FluentMigration_Test extends Common_Test
{
    public function testConstruct()
    {
        // Doesn't error
        $migration = new FluentMigration();
    }
    
    public function testConstructSaveFn()
    {
        // Save Function works
        $arg = null;
        $fn = function(FluentMigration $migration) use (&$arg) {
            $arg = $migration;
        };
        $migration = new FluentMigration($fn);
        $migration->save();
        $this->assertEquals($migration, $arg);
    }
    
    public function testSaveFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FluentMigration();
        $migration->save();
    }
    
    public function testVersion() {
        $migration = new FluentMigration();
        $this->assertEquals(null, $migration->version());
        $this->assertEquals($migration, $migration->version(2345));
        $this->assertEquals(2345, $migration->version());
    }
    
    public function testName()
    {
        $migration = new FluentMigration();
        $this->assertEquals(null, $migration->name());
        $this->assertEquals($migration, $migration->name('estt'));
        $this->assertEquals('estt', $migration->name());
    }
    
    public function testState()
    {
        $migration = new FluentMigration();
        $this->assertEquals('initial', $migration->state());
        $this->assertEquals($migration, $migration->state('success'));
        $this->assertEquals('success', $migration->state());
    }
    
    public function testDown()
    {
        $migration = new FluentMigration();
        $fn = function() {};
        $this->assertEquals(null, $migration->down());
        $this->assertEquals($migration, $migration->down($fn));
        $this->assertEquals($fn, $migration->down());
    }
    
    public function testDownFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FluentMigration();
        $migration->down('not a function');
    }
    
    public function testUp()
    {
        $migration = new FluentMigration();
        $fn = function() {};
        $this->assertEquals(null, $migration->up());
        $this->assertEquals($migration, $migration->up($fn));
        $this->assertEquals($fn, $migration->up());
    }
    
    public function testUpFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FluentMigration();
        $migration->up('not a function');
    }
    
    public function testRunDown()
    {
        $database = $this->databaseFactory();
        $migration = new FluentMigration();
        $wasCalled = false;
        $givenDatabase = null;
        $migration->down(function(Database $database) use(&$wasCalled, &$givenDatabase) {
            $wasCalled = true;
            $givenDatabase = $database;
        })->inject(array(
            'database' => $database,
        ))->runDown();
        $this->assertEquals(true, $wasCalled);
        $this->assertEquals($database, $givenDatabase);
    }
    
    public function testRunDownFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FluentMigration();
        $migration->runDown();
    }
    
    public function testRunUp()
    {
        $database = $this->databaseFactory();
        $migration = new FluentMigration();
        $wasCalled = false;
        $givenDatabase = null;
        $migration->up(function(Database $database) use(&$wasCalled, &$givenDatabase) {
            $wasCalled = true;
            $givenDatabase = $database;
        })->inject(array(
            'database' => $database,
        ))->runUp();
        $this->assertEquals(true, $wasCalled);
        $this->assertEquals($database, $givenDatabase);
    }
//    
    public function testRunUpFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FluentMigration();
        $migration->runUp();
    }
}
