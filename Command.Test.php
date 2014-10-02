<?php

class Command_Test extends Common_Migrator_Test
{
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
}
