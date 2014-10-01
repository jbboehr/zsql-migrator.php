<?php

namespace zsql\Migrator;

class Migrator
{
    /**
     * @var string
     */
    protected $classRegex = '/^Migration_?([\d]+)_?([\w\d]+)$/';
    
    /**
     * @var \zsql\Database
     */
    protected $database;
    
    /**
     * @var \zsql\Migrator\Loader
     */
    private $loader;

    /**
     * @var string
     */
    protected $migrationPath;
    
    /**
     * @var string
     */
    protected $migrationTable = 'migrations';
    
    /**
     * @var array
     */
    protected $migrations;
    
    /**
     * @var string
     */
    protected $namespace = 'Migrations\\';
    
    /**
     * Constructor
     * 
     * @param \Pimple|array $spec
     */
    public function __construct($spec)
    {
        $this->database = $spec['database'];
        if( !($this->database instanceof \zsql\Database) ) {
            throw new \Exception('Database must be instance of zsql\\Database');
        }
        
        $this->migrationPath = $spec['migrationPath'];
        
        if( isset($spec['loader']) ) {
            $this->loader = $spec['loader'];
        } else {
            $this->loader = new Loader();
        }
        if( isset($spec['namespace']) ) {
            $this->namespace = $spec['namespace'];
        }
        if( isset($spec['classRegex']) ) {
            $this->classRegex = $spec['classRegex'];
        }
        
        $this->migrations = $this->prepare();
    }
    
    /**
     * Get the migrations currently loaded
     * 
     * @return array
     */
    public function getMigrations()
    {
        return $this->migrations;
    }
    
    
    /**
     * Apply all initial migrations
     * 
     * @return \zsql\Migrator\Migrator
     */
    public function migrateLatest()
    {
        // Build a list of migrations to execute
        $todo = array();
        foreach( $this->migrations as $migration ) {
            if( $migration->state() !== 'initial' ) {
                continue;
            }
            if( $migration instanceof DatabaseMigration ) {
                // Should we alert?
                continue;
            }
            $todo[] = $migration;
        }
        
        $this->executeUp($todo);
        
        return $this;
    }
    
    /**
     * Apply a specific migration
     * 
     * @param mixed $versions
     * @return \zsql\Migrator\Migrator
     */
    public function migratePick($versions)
    {
        // Convert to array and sort
        settype($versions, 'array');
        sort($versions);
        
        // Build a list of migrations to execute
        $todo = array();
        foreach( $versions as $version ) {
            if( !isset($this->migrations[$version]) ) {
                // @todo should we alert?
                continue;
            }
            $migration = $this->migrations[$version];
            if( $migration->state() !== 'initial' ) {
                // @todo should we alert?
                continue;
            }
            if( $migration instanceof DatabaseMigration ) {
                // Should we alert?
                continue;
            }
            $todo[] = $migration;
        }
        
        $this->executeUp($todo);
        
        return $this;
    }
    
    /**
     * Retry a failed migration
     * 
     * @param mixed $versions
     * @return \zsql\Migrator\Migrator
     */
    public function migrateRetry($versions = null)
    {
        if( $versions !== null ) {
            settype($versions, 'array');
            sort($versions);
        }
        
        // Build a list of migrations to execute
        $todo = array();
        foreach( $versions as $version ) {
            if( !isset($this->migrations[$version]) ) {
                // @todo should we alert?
                continue;
            }
            $migration = $this->migrations[$version];
            if( $migration->state() !== 'failed' ) {
                // @todo should we alert?
                continue;
            }
            if( $migration instanceof DatabaseMigration ) {
                // Should we alert?
                continue;
            }
            $todo[] = $migration;
        }
        
        $this->executeUp($todo);
        
        return $this;
    }
    
    /**
     * Undo a specific migration
     * 
     * @param mixed $versions
     * @return \zsql\Migrator\Migrator
     */
    public function migrateRevert($versions)
    {
        // Convert to array and sort
        settype($versions, 'array');
        rsort($versions);
        
        // Build a list of migrations to execute
        $todo = array();
        foreach( $versions as $version ) {
            if( !isset($this->migrations[$version]) ) {
                // @todo should we alert?
                continue;
            }
            $migration = $this->migrations[$version];
            if( $migration->state() !== 'success' ) {
                // @todo should we alert?
                continue;
            }
            if( $migration instanceof DatabaseMigration ) {
                // Should we alert?
                continue;
            }
            $todo[] = $migration;
        }
        
        
        $this->executeDown($todo);
        
        return $this;
    }
    
    
    
    
    
    
    
    // Execution Utilities
    
    private function executeUp(array $migrations)
    {
        foreach( $migrations as $migration ) {
            try {
                $this->executeUpOne($migration);
                $this->markState($migration, 'success');
            } catch( \Exception $e ) {
                $this->markState($migration, 'failed');
                throw $e;
            }
        }
    }
    
    private function executeUpOne(MigrationInterface $migration)
    {
        $migration->inject(array(
            'database' => $this->database,
        ));
        $migration->runUp();
    }
    
    private function executeDown(array $migrations)
    {
        foreach( $migrations as $migration ) {
            try {
                $this->executeDownOne($migration);
                $this->markState($migration, 'initial');
            } catch( \Exception $e ) {
                $this->markState($migration, 'failed-down');
                throw $e;
            }
        }
    }
    
    private function executeDownOne(MigrationInterface $migration)
    {
        $migration->inject(array(
            'database' => $this->database,
        ));
        $migration->runDown();
    }
    
    /**
     * Mark the state of a migration in the database
     * 
     * @param type $migration
     * @param type $state
     */
    private function markState($migration, $state)
    {
        $this->database->insert()
            ->into($this->migrationTable)
            ->set('version', $migration['version'])
            ->set('name', $migration['name'])
            ->set('state', $state)
            ->onDuplicateKeyUpdate('state', $state)
            ->query();
    }
    
    
    
    // Information Utilities
    
    /**
     * Load available migrations and merge
     * 
     * @return array
     */
    private function prepare()
    {
        $migrations = $this->getMigrationsOnFileSystem();
        $db = $this->getMigrationsInDatabase();
        
        foreach( $db as $version => $dbMigration ) {
            if( isset($migrations[$version]) ) {
                $fsMigration = $migrations[$version];
                // Merge into existing
                $fsMigration->state($dbMigration->state());
            } else {
                // Create placeholder
                $migrations[$version] = $dbMigration;
            }
        }
        
        ksort($migrations);
        
        return $migrations;
    }
    
    /**
     * Get all of the migrations on the file system
     * 
     * @return array
     * @throws Exception
     */
    private function getMigrationsOnFileSystem() {
        $migrationFiles = glob($this->migrationPath);
        
        $migrations = array();
        foreach( $migrationFiles as $migrationFile ) {
            $migrations += $this->loader->loadFile($migrationFile);
        }
        
        ksort($migrations);
        
        return $migrations;
    }
    
    /**
     * Get all of the migrations recorded in the database
     * 
     * @return array
     */
    private function getMigrationsInDatabase() {
        $rows = $this->database->select()
            ->from($this->migrationTable)
            ->query()
            ->fetchAll();
        
        $migrations = array();
        foreach( $rows as $row ) {
            $migrations[$row->version] = new DatabaseMigration($row->version, 
                    $row->name, $row->state);
        }
        
        ksort($migrations);
        
        return $migrations;
    }
}