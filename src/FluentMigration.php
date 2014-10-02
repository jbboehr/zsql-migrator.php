<?php

namespace zsql\Migrator;

/**
 * This class provides a "fluent" interface for creating a migration
 */
class FluentMigration extends MigrationAbstract
{
    /**
     * @var callable
     */
    private $downFn;
    
    /**
     * @var callable
     */
    private $saveFn;
    
    /**
     * @var callable
     */
    private $upFn;
    
    /**
     * Constructor
     * 
     * @param callable $saveFn
     */
    public function __construct($saveFn = null)
    {
        $this->saveFn = $saveFn;
    }
    
    /**
     * Get or set the up function
     * 
     * @param callable $fn
     * @return \zsql\Migrator\FluentMigration
     * @throws \zsql\Migrator\Exception
     */
    public function up($fn = null)
    {
        if( null === $fn ) {
            return $this->upFn;
        }
        if( !is_callable($fn) ) {
            throw new Exception('Specified function is not callable');
        }
        $this->upFn = $fn;
        return $this;
    }
    
    /**
     * Get or set the down function
     * 
     * @param callable $fn
     * @return \zsql\Migrator\FluentMigration
     * @throws \zsql\Migrator\Exception
     */
    public function down($fn = null)
    {
        if( null === $fn ) {
            return $this->downFn;
        }
        if( !is_callable($fn) ) {
            throw new Exception('Specified function is not callable');
        }
        $this->downFn = $fn;
        return $this;
    }

    /**
     * Executes the down function
     * 
     * @return \zsql\Migrator\FluentMigration
     * @throws \zsql\Migrator\Exception
     */
    public function runDown() {
        if( !is_callable($this->downFn) ) {
            throw new Exception('Specified function is not callable');
        }
        call_user_func($this->downFn, $this->database);
        return $this;
    }

    /**
     * Executes the up function
     * 
     * @return \zsql\Migrator\FluentMigration
     * @throws \zsql\Migrator\Exception
     */
    public function runUp() {
        if( !is_callable($this->upFn) ) {
            throw new Exception('Specified function is not callable');
        }
        call_user_func($this->upFn, $this->database);
        return $this;
    }
    
    /**
     * Save the current migration in the loader database
     * 
     * @return \zsql\Migrator\FluentMigration
     * @throws \zsql\Migrator\Exception
     */
    public function save()
    {
        if( !is_callable($this->saveFn) ) {
            throw new Exception('Save function is not callable');
        }
        call_user_func($this->saveFn, $this);
        return $this;
    }
}
