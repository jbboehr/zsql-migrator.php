<?php

class CommandTest extends Common_Migrator_Test
{
    private $argv;
    
    public function setUp()
    {
        global $argv;
        $this->argv = $argv;
    }
    
    public function tearDown()
    {
        global $argv;
        $argv = $this->argv;
        putenv('MYSQL_HOST=');
        putenv('MYSQL_PORT=');
        putenv('MYSQL_USER=');
        putenv('MYSQL_PASSWORD=');
        putenv('MYSQL_DB=');
    }
    
    public function testOutput()
    {
        $expectedOutputString = "test1";
        $this->expectOutputString($expectedOutputString);
        
        $command1 = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $this->migratorFactory('migrationsB'),
        ), array());
        $this->callReflectedMethod($command1, 'output', 'test1');
        
        $msg = null;
        $command2 = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $this->migratorFactory('migrationsB'),
            'outputFn' => function($string) use (&$msg) {
                $msg .= $string;
            },
        ), array());
        $this->callReflectedMethod($command2, 'output', 'test2');
        $this->assertEquals('test2', $msg);
    }
    
    public function testLatestDry()
    {
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): up
Migration 1412129177 (TestB): up

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('latest', 'dry');
        $file = 'migrationsB';
        $table = 'migrations';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testPickDry()
    {
        $expectedOutputString = <<<EOF
Migration 1412129177 (TestB): up

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('pick', '1412129177', 'dry');
        $file = 'migrationsB';
        $table = 'migrations';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testRetryDry()
    {
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): up
Migration 1412129177 (TestB): up

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('retry', 'dry');
        $file = 'migrationsA';
        $table = 'migrationsFixtureC';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testRevertDry()
    {
        $expectedOutputString = <<<EOF
Migration 1412129177 (TestB): down
Migration 1412129062 (TestA): down

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('revert', 'dry', '1412129062', '1412129177');
        $file = 'migrationsB';
        $table = 'migrationsFixtureA';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testDisplay()
    {
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): success
Migration 1412129177 (TestB): initial

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('display');
        $file = 'migrationsA';
        $table = 'migrationsFixtureB';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testDisplayWithFilter()
    {
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): success

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $args = array('display', 'success');
        $file = 'migrationsA';
        $table = 'migrationsFixtureB';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testRunThrowsOnInvalidCommand()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        
        $args = array('invalidcommand');
        $file = 'migrationsA';
        $table = 'migrationsFixtureB';
        $command = $this->commandFactory($args, $file, $table);
        $command->run();
    }
    
    public function testMigrateBin()
    {
        global $argv;
        
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): initial
Migration 1412129177 (TestB): initial

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $path = __DIR__ . '/../bin/migrate.php';
        $command = $path . ' '
                . 'host=localhost user=zsql password=nopass ' 
                . 'db=zsql path=tests/fixtures/migrationsA.php ' 
                . 'display';
        $argv = explode(' ', $command);
        
        include $path;
    }
    
    public function testMigrateBinUsingEnv()
    {
        putenv('MYSQL_HOST=localhost');
        putenv('MYSQL_USER=zsql');
        putenv('MYSQL_PASSWORD=nopass');
        putenv('MYSQL_DATABASE=zsql');
        
        global $argv;
        
        $expectedOutputString = <<<EOF
Migration 1412129062 (TestA): initial
Migration 1412129177 (TestB): initial

EOF;
        $this->expectOutputString($expectedOutputString);
        
        $path = __DIR__ . '/../bin/migrate.php';
        $command = $path . ' '
                . 'path=tests/fixtures/migrationsA.php ' 
                . 'display';
        $argv = explode(' ', $command);
        
        include $path;
    }
    
    public function testLatestCallsMigrateLatest()
    {
        $stub = $this->getMock('\\zqsl\\Migrator\\Migrator', 
                array('migrateLatest', 'setEmitter'));
        $stub->expects($this->once())
                ->method('migrateLatest')
                ->will($this->returnValue(null));
        $stub->expects($this->once())
                ->method('setEmitter')
                ->will($this->returnValue(null));
        $command = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $stub,
        ), array('latest'));
        $command->run();
    }
    
    public function testPickCallsMigratePick()
    {
        $stub = $this->getMock('\\zqsl\\Migrator\\Migrator', 
                array('migratePick', 'setEmitter'));
        $stub->expects($this->once())
                ->method('migratePick')
                ->will($this->returnValue(null));
        $stub->expects($this->once())
                ->method('setEmitter')
                ->will($this->returnValue(null));
        $command = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $stub,
        ), array('pick', '1412129062'));
        $command->run();
    }
    
    public function testRetryCallsMigrateRetry()
    {
        $stub = $this->getMock('\\zqsl\\Migrator\\Migrator', 
                array('migrateRetry', 'setEmitter'));
        $stub->expects($this->once())
                ->method('migrateRetry')
                ->will($this->returnValue(null));
        $stub->expects($this->once())
                ->method('setEmitter')
                ->will($this->returnValue(null));
        $command = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $stub,
        ), array('retry', '1412129062'));
        $command->run();
    }
    
    public function testRevertCallsMigrateRevert()
    {
        $stub = $this->getMock('\\zqsl\\Migrator\\Migrator', 
                array('migrateRevert', 'setEmitter'));
        $stub->expects($this->once())
                ->method('migrateRevert')
                ->will($this->returnValue(null));
        $stub->expects($this->once())
                ->method('setEmitter')
                ->will($this->returnValue(null));
        $command = new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $stub,
        ), array('revert', '1412129062'));
        $command->run();
    }
    
    public function testEmitHandlerDownSuccess()
    {
        $this->expectOutputString('Migration 123 (Test): down ... Success' . PHP_EOL);
        $migration = new \zsql\Migrator\FluentMigration();
        $migration->version(123)
                ->name('Test')
                ->state('initial');
        $command = $this->commandFactory(array(), 'migrationsA');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'down-start'
        ));
        $migration->state('success');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'down-success'
        ));
    }
    
    public function testEmitHandlerDownFailed()
    {
        $this->expectOutputString('Migration 123 (Test): down ... Failed' . PHP_EOL);
        $migration = new \zsql\Migrator\FluentMigration();
        $migration->version(123)
                ->name('Test')
                ->state('initial');
        $command = $this->commandFactory(array(), 'migrationsA');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'down-start'
        ));
        $migration->state('success');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'down-failed'
        ));
    }
    
    public function testEmitHandlerUpSuccess()
    {
        $this->expectOutputString('Migration 123 (Test): up ... Success' . PHP_EOL);
        $migration = new \zsql\Migrator\FluentMigration();
        $migration->version(123)
                ->name('Test')
                ->state('initial');
        $command = $this->commandFactory(array(), 'migrationsA');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'up-start'
        ));
        $migration->state('success');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'up-success'
        ));
    }
    
    public function testEmitHandlerUpFailed()
    {
        $this->expectOutputString('Migration 123 (Test): up ... Failed' . PHP_EOL);
        $migration = new \zsql\Migrator\FluentMigration();
        $migration->version(123)
                ->name('Test')
                ->state('initial');
        $command = $this->commandFactory(array(), 'migrationsA');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'up-start'
        ));
        $migration->state('success');
        $this->callReflectedMethod($command, 'emitHandler', array(
            'migration' => $migration,
            'action' => 'up-failed'
        ));
    }
}
