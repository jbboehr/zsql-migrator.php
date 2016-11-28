<?php

namespace zsql\Migrator\Tests;

use FixtureMigration_123_LegacyFixture;
use zsql\Migrator\Tests\Fixture\FixtureMigrationInvalidName;

class LegacyMigrationTest extends Common
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
        $migration->setDatabase($this->databaseFactory());
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
        $migration->setDatabase($this->databaseFactory());
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
