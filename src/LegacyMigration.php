<?php

namespace zsql\Migrator;

/**
 * Provides a legacy class name based interface for creating migrations
 */
abstract class LegacyMigration extends MigrationAbstract
{
    /**
     * @var string
     */
    protected $classRegex = '/Migration_?([\d]+)_?([\w\d]+)$/';
    
    /**
     * Constructor
     * 
     * @throws \zsql\Migrator\Exception
     */
    public function __construct()
    {
        $class = get_class($this);
        $lastPos = strrpos($class, '\\');
        if( false !== $lastPos ) {
            $classPart = substr($class, $lastPos + 1);
        } else {
            $classPart = $class;
        }
        $matches = null;
        if( !preg_match($this->classRegex, $classPart, $matches ) ) {
            throw new Exception('Invalid legacy migration class fragment: ' . $classPart);
        }
        $this->version = $matches[1];
        $this->name = $matches[2];
    }
    
    /**
     * Executes the down function
     * 
     * @return \zsql\Migrator\LegacyMigration
     * @throws \zsql\Migrator\Exception
     */
    public function runDown() {
        if( !$this->database ) {
            throw new Exception('Cannot execute down without a database');
        }
        $this->down();
        return $this;
    }

    /**
     * Executes the up function
     * 
     * @return \zsql\Migrator\LegacyMigration
     * @throws \zsql\Migrator\Exception
     */
    public function runUp() {
        if( !$this->database ) {
            throw new Exception('Cannot execute up without a database');
        }
        $this->up();
        return $this;
    }
    
    
    
    // Abstract methods
    
    abstract public function down();
    
    abstract public function up();

}
