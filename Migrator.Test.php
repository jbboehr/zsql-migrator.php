<?php

class Migrator_Test extends Common_Test
{
    public function migratorFactory($file, $table = null)
    {
        if( $table === null ) {
            $table = 'migrations';
        }
        return new zsql\Migrator\Migrator(array(
            'database' => $this->databaseFactory(),
            'migrationPath' => __DIR__ . '/../fixtures/' . $file . '.php',
            'migrationTable' => $table,
        ));
    }
    
    public function testMigrationsEmptyTable()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA');
        $migrators[] = $this->migratorFactory('migrationsB');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertCount(2, $latest);
            $this->assertEquals(1412129062, $latest[0]->version());
            $this->assertEquals(1412129177, $latest[1]->version());

            // Test pick 1
            $pick1 = $migrator->calculatePick(1412129062);
            $this->assertCount(1, $pick1);
            $this->assertEquals(1412129062, $pick1[0]->version());

            // Test pick 2
            $pick2 = $migrator->calculatePick(1412129177);
            $this->assertCount(1, $pick2);
            $this->assertEquals(1412129177, $pick2[0]->version());

            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertEmpty($retry);

            // Test revert 1
            $revert1 = $migrator->calculateRevert(1412129062);
            $this->assertEmpty($revert1);

            // Test revert 2
            $revert2 = $migrator->calculateRevert(1412129177);
            $this->assertEmpty($revert2);
        }
    }
    
    public function testMigrationsFullSuccess()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA', 'migrationsFixtureA');
        $migrators[] = $this->migratorFactory('migrationsB', 'migrationsFixtureA');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertEmpty($latest);
            
            // Test pick 1
            $pick1 = $migrator->calculatePick(1412129062);
            $this->assertEmpty($pick1);
            
            // Test pick 2
            $pick2 = $migrator->calculatePick(1412129177);
            $this->assertEmpty($pick2);
            
            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertEmpty($retry);
            
            // Test revert 1
            $revert1 = $migrator->calculateRevert(1412129062);
            $this->assertCount(1, $revert1);
            $this->assertEquals(1412129062, $revert1[0]->version());

            // Test revert 2
            $revert2 = $migrator->calculateRevert(1412129177);
            $this->assertCount(1, $revert2);
            $this->assertEquals(1412129177, $revert2[0]->version());
        }
    }
    
    public function testMigrationsHalf()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA', 'migrationsFixtureB');
        $migrators[] = $this->migratorFactory('migrationsB', 'migrationsFixtureB');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertCount(1, $latest);
            $this->assertEquals(1412129177, $latest[0]->version());
            
            // Test pick 1
            $pick1 = $migrator->calculatePick(1412129062);
            $this->assertEmpty($pick1);
            
            // Test pick 2
            $pick2 = $migrator->calculatePick(1412129177);
            $this->assertCount(1, $pick2);
            $this->assertEquals(1412129177, $pick2[0]->version());
            
            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertEmpty($retry);
            
            // Test revert 1
            $revert1 = $migrator->calculateRevert(1412129062);
            $this->assertCount(1, $revert1);
            $this->assertEquals(1412129062, $revert1[0]->version());

            // Test revert 2
            $revert2 = $migrator->calculateRevert(1412129177);
            $this->assertEmpty($revert2);
        }
    }
    
    public function testMigrationsRetryAll()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA', 'migrationsFixtureC');
        $migrators[] = $this->migratorFactory('migrationsB', 'migrationsFixtureC');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertEmpty($latest);
            
            // Test pick 1
            $pick1 = $migrator->calculatePick(1412129062);
            $this->assertEmpty($pick1);
            
            // Test pick 2
            $pick2 = $migrator->calculatePick(1412129177);
            $this->assertEmpty($pick2);
            
            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertCount(2, $retry);
            $this->assertEquals(1412129062, $retry[0]->version());
            $this->assertEquals(1412129177, $retry[1]->version());
            
            // Test revert 1
            $revert1 = $migrator->calculateRevert(1412129062);
            $this->assertEmpty($revert1);

            // Test revert 2
            $revert2 = $migrator->calculateRevert(1412129177);
            $this->assertEmpty($revert2);
        }
        
    }
}
