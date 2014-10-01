<?php

namespace zsql\Migrator;


class FluentMigration extends MigrationAbstract
{
    private $downFn;
    
    private $saveFn;
    
    private $upFn;
    
    public function __construct($saveFn = null)
    {
        $this->saveFn = $saveFn;
    }
    
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

    public function runDown() {
        if( !is_callable($this->downFn) ) {
            throw new Exception('Specified function is not callable');
        }
        call_user_func($this->downFn, $this->database);
        return $this;
    }

    public function runUp() {
        if( !is_callable($this->upFn) ) {
            throw new Exception('Specified function is not callable');
        }
        call_user_func($this->upFn, $this->database);
        return $this;
    }
    
    public function save()
    {
        if( !is_callable($this->saveFn) ) {
            throw new Exception('Save function is not callable');
        }
        call_user_func($this->saveFn, $this);
        return $this;
    }
}