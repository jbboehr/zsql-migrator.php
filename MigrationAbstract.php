<?php

namespace zsql\Migrator;

use zsql\Database;

abstract class MigrationAbstract implements MigrationInterface
{
    /**
     * @var \zsql\Database
     */
    protected $database;
    
    protected $name;
    
    protected $state;
    
    protected $version;
    
    public function inject($container) {
        if( !isset($container['database']) ||
                !($container['database'] instanceof Database) ) {
            throw new Exception('Must specify a database in inject!');
        }
        $this->database = $container['database'];
        return $this;
    }
    public function name($name = null)
    {
        if( null === $name ) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }
    
    public function state($state = null) {
        if( null === $state ) {
            return ($this->state ?: 'initial');
        }
        $this->state = $state;
        return $this;
    }
    
    public function version($version = null)
    {
        if( null === $version ) {
            return $this->version;
        }
        $this->version = $version;
        return $this;
    }
}
