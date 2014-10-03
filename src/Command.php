<?php

namespace zsql\Migrator;

class Command
{
    private $commands = array();
    
    private $isDry;
    
    /**
     * @var \zsql\Migrator\Migrator
     */
    private $migrator;
    
    private $params = array();
    
    private $outputFn;
    
    /**
     * @var \zsql\Database
     */
    protected $database;
    
    public function __construct($spec, $args)
    {
        $this->parseArgs($args);
        $this->database = $this->getDatabaseForConstructor($spec);
        $this->migrator = $this->getMigratorForConstructor($spec);
        $this->isDry = (boolean) $this->getSpliceCommand('dry');
        if( isset($spec['outputFn']) && is_callable($spec['outputFn']) ) {
            $this->outputFn = $spec['outputFn'];
        }
        $this->migrator->setEmitter(array($this, 'emitHandler'));
    }
    
    public function run()
    {
        reset($this->commands);
        $primaryCommand = current($this->commands);
        if( !in_array($primaryCommand, array('latest', 'pick', 'revert', 'retry', 'display')) ) {
            throw new Exception('Invalid command: ' . $primaryCommand);
        }
        array_shift($this->commands);
        $this->$primaryCommand();
    }
    
    public function latest()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateLatest();
            $this->printMigrations($todo, 'latest');
        } else {
            $this->migrator->migrateLatest();
        }
    }
    
    public function display()
    {
        $migrations = $this->migrator->getMigrations();
        $filter = array_shift($this->commands);
        foreach( $migrations as $migration ) {
            if( null === $filter || $filter === 'all' ) {
                $this->printMigration($migration, $migration->state());
            } else if( $filter === $migration->state() ) {
                $this->printMigration($migration, $migration->state());
            }
        }
    }
    
    public function pick()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculatePick($this->commands);
            $this->printMigrations($todo, 'pick');
        } else {
            $this->migrator->migratePick($this->commands);
        }
    }
    
    public function revert()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateRevert($this->commands);
            $this->printMigrations($todo, 'revert');
        } else {
            $this->migrator->migrateRevert($this->commands);
        }
    }
    
    public function retry()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateRetry($this->commands);
            $this->printMigrations($todo, 'retry');
        } else {
            $this->migrator->migrateRetry($this->commands);
        }
    }
    
    
    
    
    
    
    
    
    // Printers
    
    public function emitHandler($payload)
    {
        $migration = $payload['migration'];
        $action = $payload['action'];
        if( $action === 'up-start' || $action === 'down-start' ) {
            $this->output(sprintf('Migration %d (%s): %s ... ', 
                    $migration->version(), $migration->name(), 
                    str_replace('-start', '', $action)));
        } else if( $action === 'up-success' || $action === 'down-success' ) {
            $this->output(sprintf('Success' . PHP_EOL, $action));
        } else if( $action === 'up-failed' || $action === 'down-failed' ) {
            $this->output(sprintf('Failed' . PHP_EOL, $action));
        }
    }
    
    public function output($string)
    {
        if( $this->outputFn ) {
            call_user_func($this->outputFn, $string);
        } else {
            echo $string;
        }
    }
    
    private function printMigration(MigrationInterface $migration, $tag)
    {
        $out = sprintf("Migration %d (%s): %s\n", $migration->version(),
                $migration->name(), $tag);
        $this->output($out);
    }
    
    private function printMigrations(array $migrations, $action)
    {
        $direction = (in_array($action, array('latest', 'pick', 'retry')) ? 'up' : 'down');
        
        foreach( $migrations as $migration ) {
            $this->printMigration($migration, $direction);
        }
    }
    
    
    // Utilities
    
    private function getDatabaseForConstructor($spec)
    {
        if( isset($spec['database']) && 
                $spec['database'] instanceof \zsql\Database ) {
            return $spec['database'];
        }
        
        $host = $this->readParam('h', 'host');
        $port = $this->readParam(null, 'port');
        $user = $this->readParam('u', 'user');
        $password = $this->readParam('p', 'password');
        $dbname = $this->readParam('db', 'database');
        
        $mysqli = new \mysqli($host, $user, $password, $dbname, $port);
        return new \zsql\Database($mysqli);
    }
    
    private function getMigratorForConstructor($spec)
    {
        if( isset($spec['migrator']) /*&&
                $spec['migrator'] instanceof \zsql\Migrator\Migrator*/ ) {
            return $spec['migrator'];
        }
        
        $self = $this;
        $path = $this->readParam('path', 'migrations', './schema/migration*.php');
        return new Migrator(array(
            'database' => $this->database,
            'migrationPath' => $path,
            'emit' => array($this, 'emitHandler'),
        ));
    }
    
    private function getSpliceCommand($key, $default = null)
    {
        $index = array_search($key, $this->commands);
        if( $index === false ) {
            return $default;
        }
        $value = $this->commands[$index];
        array_splice($this->commands, $index, 1);
        return $value;
    }
    
    private function parseArgs($args)
    {
        foreach( $args as $arg ) {
            if( false !== strpos($arg, '=') ) {
                list($k, $v) = explode('=', $arg, 2);
                $this->params[$k] = $v;
            } else {
                $this->commands[] = $arg;
            }
        }
    }
    
    private function readParam($short, $long, $default = null)
    {
        $env = getenv('MYSQL_' . strtoupper($long));
        if( $long && isset($this->params[$long]) ) {
            return $this->params[$long];
        } else if( $short && isset($this->params[$short]) ) {
            return $this->params[$short];
        } else if( $env ) {
            return $env;
        } else {
            return $default;
        }
    }
}