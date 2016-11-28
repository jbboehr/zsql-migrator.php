<?php

namespace zsql\Migrator\Tests;

use zsql\Migrator\Loader;
use zsql\Migrator\FluentMigration;

class LoaderTest extends Common
{
    private $loader;

    public function setUp()
    {
        parent::setUp();
        $this->loader = new Loader();
        $this->setReflectedPropertyValue($this->loader, 'currentFile', null);
    }

    public function testGetInstanceFails()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        Loader::_();
    }

    public function testGetPhpClasses()
    {
        $loader = new Loader();
        $classes = $this->callReflectedMethod($loader, 'file_get_php_classes',
                __DIR__ . '/fixtures/migrationsA.php');
        $this->assertEquals(array(
            0 => '\\zsql\\Migrator\\Tests\\Fixtures\\MigrationsA\\Migration1412129062_TestA',
            1 => '\\zsql\\Migrator\\Tests\\Fixtures\\MigrationsA\\Migration1412129177_TestB'
        ), $classes);
    }

    public function testCloneMigrations()
    {
        $a = new FluentMigration();
        $a->version(1);
        $b = new FluentMigration();
        $b->version(2);
        $migrations = array(
            $a->version() => $a,
            $b->version() => $b
        );

        $loader = new Loader();
        $clones = $this->callReflectedMethod($loader, 'cloneMigrations', $migrations);
        $this->assertCount(2, $clones);
        $this->assertEquals($a, $clones[1]);
        $this->assertNotSame($a, $clones[1]);
        $this->assertEquals($b, $clones[2]);
        $this->assertNotSame($b, $clones[2]);

        $migrations2 = array();
        $clones2 = $this->callReflectedMethod($loader, 'cloneMigrations', $migrations2);
        $this->assertEquals($clones2, $migrations2);
    }

    public function testSaveMigrationFailsWithoutCurrentFile()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception', 'No current migration file');
        $loader = new Loader();
        $loader->migration()
                ->save();
    }

    public function testSaveMigrationFailsWithoutVersion()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception', 'Migration must have a version');
        $loader = new Loader();
        $this->setReflectedPropertyValue($loader, 'currentFile', 'Loader.Test.php');
        $loader->migration()->save();
    }

    public function testSaveMigrationFailsWithDuplicateVersion()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception', 'Must not have migration with duplicate version');
        $loader = new Loader();
        $this->setReflectedPropertyValue($loader, 'currentFile', 'Loader.Test.php');
        $loader->migration()->version(1)->save();
        $loader->migration()->version(1)->save();
    }

    public function testLoadFileExistingMigrations()
    {
        $loader = new Loader();
        $this->setReflectedPropertyValue($loader, 'currentFile', __METHOD__);
        $migration = $loader->migration()->version(1)->save();
        $clones = $loader->loadFile(__METHOD__);
        $this->assertEquals($migration, $clones[1]);
        $this->assertNotSame($migration, $clones[1]);
    }

    public function testLoadFileException()
    {
        $loader = new Loader();
        try {
          $migrations = $loader->loadFile(__DIR__ . '/fixtures/loaderA.php');
          $this->assertEquals(false, true, 'Did not throw exception');
        } catch( \Exception $e ) {
            $this->assertEquals($e->getMessage(), 'testing exception in constructor');
            $this->assertEquals(null, $this->getReflectedPropertyValue($loader, 'currentFile'));
        }

    }
}
