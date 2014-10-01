<?php

namespace zsql\Migrator;

class DatabaseMigration extends \zsql\Migrator\MigrationAbstract
{
    public function __construct($name, $version, $state)
    {
        $this->name = (string) $name;
        $this->version = (integer) $version;
        $this->state = (string) $state ?: 'initial';
    }

    public function runDown() {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }

    public function runUp() {
        throw new Exception('Migration ' . $this->version() . ' was only recorded in the database and cannot be run');
    }
}
