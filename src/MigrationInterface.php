<?php

namespace zsql\Migrator;

use zsql\Adapter\AdapterAwareInterface;

/**
 * Interface for migration classes
 */
interface MigrationInterface extends AdapterAwareInterface
{
    /**
     * Get or set the name of the migration
     * 
     * @param string $name
     */
    public function name($name = null);
    
    /**
     * Run the up queries. Should not be called before inject
     * 
     * @throws \zsql\Migrator\Exception
     */
    public function runUp();
    
    /**
     * Run the down queries. Should not be called before injext
     * 
     * @throws \zsql\Migrator\Exception
     */
    public function runDown();
    
    /**
     * Get or set the migration state
     * 
     * @param string $state
     */
    public function state($state = null);
    
    /**
     * Get or set the version
     * 
     * @param integer $version
     */
    public function version($version = null);
}
