<?php

namespace zsql\Migrator;

/**
 * This class represents a migration that has been recorded in the database
 */
class DatabaseMigration extends MigrationAbstract
{
    /**
     * Constructor
     * 
     * @param integer $version
     * @param string $name
     * @param string $state
     */
    public function __construct($version, $name, $state = null)
    {
        $this->version = (integer) $version;
        $this->name = (string) $name;
        $this->state = (string) ($state ?: 'initial');
    }

    /**
     * Execute the down function
     * 
     * @throws Exception
     */
    public function runDown()
    {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }
    
    /**
     * Execute the up function
     * 
     * @throws Exception
     */
    public function runUp()
    {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }
}
