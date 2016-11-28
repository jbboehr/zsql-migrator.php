<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Common.php';

class FixtureMigration_123_LegacyFixture extends zsql\Migrator\LegacyMigration
{
    public $downRun = false;
    public $upRun = false;


    public function down() {
        $this->downRun = true;
    }

    public function up() {
        $this->upRun = true;
    }
}
