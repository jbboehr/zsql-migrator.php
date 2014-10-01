<?php

namespace zsql\Migrator;

class DatabaseMigration extends \zsql\Migrator\MigrationAbstract
{
    public function __construct($version, $name, $state = null)
    {
        $this->version = (integer) $version;
        $this->name = (string) $name;
        $this->state = (string) ($state ?: 'initial');
    }

    public function runDown() {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }

    public function runUp() {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }
}
