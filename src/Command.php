<?php

namespace zsql\Migrator;

class Command
{
    /**
     * @var array
     */
    private $commands = array();
    
    /**
     * @var boolean
     */
    private $isDry;
    
    /**
     * @var boolean
     */
    private $isHelp;
    
    /**
     * @var \zsql\Migrator\Migrator
     */
    private $migrator;
    
    /**
     * @var array
     */
    private $params = array();
    
    /**
     * @var callable
     */
    private $outputFn;
    
    /**
     * @var \zsql\Database
     */
    protected $database;
    
    /**
     * Constructor
     * 
     * @param array $spec
     * @param array $args
     */
    public function __construct($spec, $args)
    {
        $this->parseArgs($args);
        $this->database = $this->getDatabaseForConstructor($spec);
        $this->migrator = $this->getMigratorForConstructor($spec);
        $this->isDry = (boolean) $this->getSpliceCommand('dry');
        $this->isHelp = (boolean) $this->getSpliceCommand('help');
        if( isset($spec['outputFn']) && is_callable($spec['outputFn']) ) {
            $this->outputFn = $spec['outputFn'];
        }
        $this->migrator->setEmitter(array($this, 'emitHandler'));
    }
    
    public function run()
    {
        if( empty($this->commands) ) {
            $this->commands = array('help');
        }
        
        reset($this->commands);
        $primaryCommand = array_shift($this->commands);
        $method = $primaryCommand . 'Action';
        if( !method_exists($this, $method) ) {
            throw new Exception('Invalid command: ' . $primaryCommand);
        }
        $this->$method();
    }
    
    public function helpAction()
    {
        echo <<<EOF
Usage:
 migrate help

Commands:
 latest         Executes all unexecuted migrations
 list           Lists all known migrations
 pick           Executes the migrations specified
 revert         Reverts the migrations specified
 retry          Attempts to re-run failed migrations specified


EOF;
    }
    
    public function latestAction()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateLatest();
            $this->printMigrations($todo, 'latest');
        } else if( $this->isHelp ) {
            echo <<<EOF
Usage:
 migrate latest [dry]

Arguments:
 dry        Will print the operations that will be executed

Help:
 Executes all unexecuted migrations in order from lowest to highest ID.


EOF;
        } else {
            $this->migrator->migrateLatest();
        }
    }
    
    public function displayAction()
    {
        // Alias for list
        $this->listAction();
    }
    
    public function listAction()
    {
        if( $this->isHelp ) {
            echo <<<EOF
Usage:
 migrate list

Help:
 Prints a list of all known migrations.


EOF;
        } else {
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
    }
    
    public function pickAction()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculatePick($this->commands);
            $this->printMigrations($todo, 'pick');
        } else if( $this->isHelp ) {
            echo <<<EOF
Usage:
 migrate pick <ID> [<ID2> ...] [dry]

Arguments:
 dry        Will print the operations that will be executed

Help:
 Executes the migrations specified by ID.


EOF;
        } else {
            $this->migrator->migratePick($this->commands);
        }
    }
    
    public function revertAction()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateRevert($this->commands);
            $this->printMigrations($todo, 'revert');
        } else if( $this->isHelp ) {
            echo <<<EOF
Usage:
 migrate revert <ID> [<ID2>, ...] [dry]

Arguments:
 dry        Will print the operations that will be executed

Help:
 Reverts the migrations specified by ID.


EOF;
        } else {
            $this->migrator->migrateRevert($this->commands);
        }
    }
    
    public function retryAction()
    {
        if( $this->isDry ) {
            $todo = $this->migrator->calculateRetry($this->commands);
            $this->printMigrations($todo, 'retry');
        } else if( $this->isHelp ) {
            echo <<<EOF
Usage:
 migrate retry <ID> [<ID2>, ...] [dry]

Arguments:
 dry        Will print the operations that will be executed

Help:
 Attempts to re-run failed migrations specified by ID.


EOF;
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
        if( $mysqli->connect_error ) {
            throw new \mysqli_sql_exception($mysqli->connect_error);
        }
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