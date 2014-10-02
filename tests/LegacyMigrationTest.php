<?php

use zsql\Database;
use zsql\Migrator\FluentMigration;

class LegacyMigrationTest extends Common_Test
{
    public function testConstruct()
    {
        $migration = new FixtureMigration_123_LegacyFixture();
        $this->assertEquals(123, $migration->version());;
        $this->assertEquals('LegacyFixture', $migration->name());
    }
    
    public function testConstructFailsWithInvalidClassName()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        new FixtureMigrationInvalidName();
    }
    
    public function testRunDown()
    {
        $migration = new FixtureMigration_123_LegacyFixture();
        $migration->inject(array(
            'database' => $this->databaseFactory(),
        ));
        $migration->runDown();
        $this->assertEquals(true, $migration->downRun);
    }
    
    public function testRunDownFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FixtureMigration_123_LegacyFixture();
        $migration->runDown();
    }
    
    public function testRunUp()
    {
        $migration = new FixtureMigration_123_LegacyFixture();
        $migration->inject(array(
            'database' => $this->databaseFactory(),
        ));
        $migration->runUp();
        $this->assertEquals(true, $migration->upRun);
    }
    
    public function testRunUpFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new FixtureMigration_123_LegacyFixture();
        $migration->runUp();
    }
}
