<?php

namespace zsql\Migrator\Tests;

class MigratorIntegrationTest extends Common
{
    public function setup()
    {
        $d = $this->databaseFactory();
        $d->query('TRUNCATE TABLE `zsql`.`migrationsFixtureE`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtesta`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtestb`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtestc`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtestd`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationteste`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtestf`');
        $d->query('DROP TABLE IF EXISTS `zsql`.`migrationtestg`');
    }

    public function testIntegration()
    {
        $database = $this->databaseFactory();
        $migrator = $this->migratorFactory('migrationsC', 'migrationsFixtureE');

        // First, let's migrate all the way up
        $migrator->migrateLatest();

        // Check that all five migrations completed successfully
        $rows = $database->select()
                ->from('migrationsFixtureE')
                ->query()
                ->fetchAll();
        $this->assertCount(5, $rows);
        foreach( $rows as $row ) {
            $this->assertEquals('success', $row->state);
        }
        $blegh = $database->query('SHOW TABLES')->fetchAll(\zsql\Result::FETCH_COLUMN);
        $this->assertContains('migrationtesta', $blegh);
        $this->assertContains('migrationtestb', $blegh);
        $this->assertContains('migrationtestc', $blegh);
        $this->assertContains('migrationtestd', $blegh);
        $this->assertContains('migrationteste', $blegh);


        // Now let's revert a couple migrations
        $migrator->migrateRevert(array(
            1412129177,
            1412292439
        ));

        // Check that all five migrations completed successfully
        $rows = $database->select()
                ->from('migrationsFixtureE')
                ->query()
                ->fetchAll();
        $this->assertCount(5, $rows);
        foreach( $rows as $row ) {
            if( $row->version == 1412129177 ||
                $row->version == 1412292439 ) {
                $this->assertEquals('initial', $row->state);
            } else {
                $this->assertEquals('success', $row->state);
            }
        }
        $blegh = $database->query('SHOW TABLES')->fetchAll(\zsql\Result::FETCH_COLUMN);
        $this->assertContains('migrationtesta', $blegh);
        $this->assertNotContains('migrationtestb', $blegh);
        $this->assertContains('migrationtestc', $blegh);
        $this->assertNotContains('migrationtestd', $blegh);
        $this->assertContains('migrationteste', $blegh);


        // Now let's pick each migration
        $migrator->migratePick(array(
            1412129177,
            1412292439
        ));

        // Check that all five migrations completed successfully
        $rows = $database->select()
                ->from('migrationsFixtureE')
                ->query()
                ->fetchAll();
        $this->assertCount(5, $rows);
        foreach( $rows as $row ) {
            $this->assertEquals('success', $row->state);
        }
        $blegh = $database->query('SHOW TABLES')->fetchAll(\zsql\Result::FETCH_COLUMN);
        $this->assertContains('migrationtesta', $blegh);
        $this->assertContains('migrationtestb', $blegh);
        $this->assertContains('migrationtestc', $blegh);
        $this->assertContains('migrationtestd', $blegh);
        $this->assertContains('migrationteste', $blegh);





        // Let's mark a migration as failed an then retry it
        $database->query('DROP TABLE migrationtestd');
        $database->query("UPDATE migrationsFixtureE SET state = 'failed' "
                . "WHERE version = 1412292439 LIMIT 1");
        $migrator = $this->migratorFactory('migrationsC', 'migrationsFixtureE');

        $migrator->migrateRetry();

        $row = $database->select()
                ->from('migrationsFixtureE')
                ->where('version', 1412292439)
                ->query()
                ->fetchRow();
        $this->assertEquals('success', $row->state);
        $blegh = $database->query('SHOW TABLES')->fetchAll(\zsql\Result::FETCH_COLUMN);
        $this->assertContains('migrationtestd', $blegh);
    }
}
