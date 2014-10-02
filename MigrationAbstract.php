<?php

namespace zsql\Migrator;

use zsql\Database;

/**
 * Abstract migration class
 */
abstract class MigrationAbstract implements MigrationInterface
{
    /**
     * @var \zsql\Database
     */
    protected $database;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $state;
    
    /**
     * @var integer
     */
    protected $version;
    
    /**
     * Injector for dependencies
     * 
     * @param \Pimple|array $container
     * @return \zsql\Migrator\MigrationAbstract
     * @throws \zsql\Migrator\Exception
     */
    public function inject($container) {
        if( !isset($container['database']) ||
                !($container['database'] instanceof Database) ) {
            throw new Exception('Must specify a database in inject!');
        }
        $this->database = $container['database'];
        return $this;
    }
    
    /**
     * Get or set the migration name
     * 
     * @param string $name
     * @return \zsql\Migrator\MigrationAbstract
     */
    public function name($name = null)
    {
        if( null === $name ) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get or set the migration state
     * 
     * @param string $state
     * @return \zsql\Migrator\MigrationAbstract
     */
    public function state($state = null) {
        if( null === $state ) {
            return ($this->state ?: 'initial');
        }
        $this->state = $state;
        return $this;
    }
    
    /**
     * Get or set the migration version
     * 
     * @param integer $version
     * @return \zsql\Migrator\MigrationAbstract
     */
    public function version($version = null)
    {
        if( null === $version ) {
            return $this->version;
        }
        $this->version = $version;
        return $this;
    }
    
    public function __toString()
    {
        return 'Migration:'
             . ' version=' . $this->version
             . ' name=' . $this->name
             . ' state=' . $this->state;
    }
}
