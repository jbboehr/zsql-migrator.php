<?php

use zsql\Migrator\DatabaseMigration;

class DatabaseMigrationTest extends Common_Test
{
    public function testVersion() {
        $migration = new DatabaseMigration(1234, 'test', 'initial');
        $this->assertEquals(1234, $migration->version());
        $this->assertEquals($migration, $migration->version(2345));
        $this->assertEquals(2345, $migration->version());
    }
    
    public function testName()
    {
        $migration = new DatabaseMigration(1234, 'test', 'initial');
        $this->assertEquals('test', $migration->name());
        $this->assertEquals($migration, $migration->name('estt'));
        $this->assertEquals('estt', $migration->name());
    }
    
    public function testState()
    {
        $migration = new DatabaseMigration(1234, 'test', 'initial');
        $this->assertEquals('initial', $migration->state());
        $this->assertEquals($migration, $migration->state('success'));
        $this->assertEquals('success', $migration->state());
    }
    
    public function testRunDown()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new DatabaseMigration(1234, 'test', 'initial');
        $migration->runDown();
    }
    
    public function testRunUp()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migration = new DatabaseMigration(1234, 'test', 'initial');
        $migration->runUp();
    }
}
