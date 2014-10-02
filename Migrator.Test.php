<?php

class Migrator_Test extends Common_Migrator_Test
{
    
    public function testConstructorThrowsWhenNoDatabase()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        new \zsql\Migrator\Migrator(array());
    }
    
    public function testConstructorCustomLoader()
    {
       $loader = new zsql\Migrator\Loader();
       $migrator = new zsql\Migrator\Migrator(array(
           'database' => $this->databaseFactory(),
           'loader' => $loader,
       ));
       $this->assertSame($loader, $this->getReflectedPropertyValue($migrator, 'loader'));
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
        }
    }
    
    public function testMigrationsRetry()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA', 'migrationsFixtureC');
        $migrators[] = $this->migratorFactory('migrationsB', 'migrationsFixtureC');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertEmpty($latest);
            
            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertCount(2, $retry);
            $this->assertEquals(1412129062, $retry[0]->version());
            $this->assertEquals(1412129177, $retry[1]->version());
            
            // Test retry 1
            $retry1 = $migrator->calculateRetry(1412129062);
            $this->assertCount(1, $retry1);
            $this->assertEquals(1412129062, $retry1[0]->version());
            
            // Test retry 2
            $retry2 = $migrator->calculateRetry(1412129177);
            $this->assertCount(1, $retry2);
            $this->assertEquals(1412129177, $retry2[0]->version());
        }
    }
    
    public function testMigrationsWithDatabaseOnlyMigration()
    {
        $migrators = array();
        $migrators[] = $this->migratorFactory('migrationsA', 'migrationsFixtureD');
        $migrators[] = $this->migratorFactory('migrationsB', 'migrationsFixtureD');
        
        foreach( $migrators as $migrator ) {
            // Test latest
            $latest = $migrator->calculateLatest();
            $this->assertCount(1, $latest);
            $this->assertEquals(1412129177, $latest[0]->version());
            
            // Test pick 1
            $pick1 = $migrator->calculatePick(1412129177);
            $this->assertCount(1, $pick1);
            $this->assertEquals(1412129177, $pick1[0]->version());
            
            // Test retry
            $retry = $migrator->calculateRetry();
            $this->assertEmpty($retry);
            
            // Test revert
            $revert = $migrator->calculateRevert(1412129062);
            $this->assertCount(1, $revert);
            $this->assertEquals(1412129062, $revert[0]->version());
        }
    }
    
    public function testCalculatePickThrowsOnInvalidVersion()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureC');
        $migrator->calculatePick(123123123123);
    }
    
    public function testCalculatePickThrowsOnInvalidState()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureA');
        $migrator->calculatePick(1412129062);
    }
    
    public function testCalculatePickThrowsOnDatabaseOnlyMigration()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureD');
        $migrator->calculatePick(1412225918);
    }
    
    public function testCalculateRetryThrowsOnInvalidVersion()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureC');
        $migrator->calculateRetry(123123123123);
    }
    
    public function testCalculateRetryThrowsOnInvalidState()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureB');
        $migrator->calculateRetry(1412129062);
    }
    
    public function testCalculateRetryThrowsOnDatabaseOnlyMigration()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureD');
        $migrator->calculateRetry(1412227465);
    }
    
    public function testCalculateRevertThrowsOnInvalidVersion()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureC');
        $migrator->calculateRevert(123123123123);
    }
    
    public function testCalcualteRevertThrowsOnDatabaseOnlyMigration()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureD');
        $migrator->calculateRevert(1412225787);
    }
    
    public function testCalculateRevertThrowsInInvalidState()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA');
        $migrator->calculateRevert(1412129062);
    }
    
    public function testCalculateRevertThrowsInInvalidState2()
    {
        $this->setExpectedException('\\zsql\\Migrator\\Exception');
        $migrator = $this->migratorFactory('migrationsA', 'migrationsFixtureB');
        $migrator->calculateRevert(1412129177);
    }
}
