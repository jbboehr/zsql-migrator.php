<?php

namespace zsql\Migrator;

/**
 * Loader for migration files
 */
class Loader
{    
    /**
     * @var string
     */
    static private $currentFile;
    
    /**
     * @var \zsql\Migrator\Loader
     */
    static private $instance;
    
    /**
     * @var array
     */
    static private $migrations;
    
    /**
     * Instance method
     * 
     * @return \zsql\Migrator\Loader
     * @throws \zsql\Migrator\Exception
     */
    static public function _()
    {
        if( null === self::$instance ) {
            throw new Exception('No current migration loader instance');
        }
        return self::$instance;
    }
    
    /**
     * Load migrations in specified file
     * 
     * @param string $file
     * @return array
     * @throws \zsql\Migrator\Exception
     */
    public function loadFile($file) {
        if( isset(self::$migrations[$file]) ) {
            return $this->cloneMigrations(self::$migrations[$file]);
        }
        
        self::$instance = $this;
        self::$currentFile = $file;
        try {
            $this->loadFileInternal($file);
            self::$currentFile = null;
            self::$instance = null;
        } catch( \Exception $e ) {
            self::$currentFile = null;
            self::$instance = null;
            throw $e;
        }
        return $this->cloneMigrations(self::$migrations[$file]);
    }
    
    /**
     * Load migrations in a file (internal)
     * 
     * @param string $file
     */
    private function loadFileInternal($file)
    {
        include_once $file;
        $classes = $this->file_get_php_classes($file);
        foreach( $classes as $class ) {
            if( is_subclass_of($class, '\\zsql\\Migrator\\MigrationInterface') ) {
                $this->saveMigration(new $class());
            }
        }
    }
    
    /**
     * Fluent interface accessor for migration construction
     * 
     * @return \zsql\Migrator\FluentMigration
     */
    public function migration() {
        $self = $this;
        $onSave = function(MigrationInterface $migration) use ($self) {
            $self->saveMigration($migration);
        };
        return new FluentMigration($onSave);
    }
    
    /**
     * Save migration in the loader database
     * 
     * @param \zsql\Migrator\MigrationInterface $migration
     * @return \zsql\Migrator\Loader
     * @throws \zsql\Migrator\Exception
     */
    public function saveMigration(MigrationInterface $migration)
    {
        if( null === self::$currentFile ) {
            throw new Exception('No current migration file');
        }
        $version = $migration->version();
        if( null === $version ) {
            throw new Exception('Migration must have a version');
        }
        if( isset(self::$migrations[self::$currentFile][$version]) ) {
            throw new Exception('Must not have migration with duplicate version');
        }
        self::$migrations[self::$currentFile][$version] = $migration;
        return $this;
    }
    
    
    
    
    // Utilties
    
    /**
     * Clone an array of migrations
     * 
     * @param array $migrations
     * @return array
     */
    private function cloneMigrations(array $migrations = null)
    {
        if( !$migrations ) {
            return array();
        }
        
        $arr = array();
        foreach( $migrations as $migration ) {
            $arr[$migration->version()] = clone $migration;
        }
        return $arr;
    }
    
    /**
     * Get a list of php classes in specified file
     * 
     * @param string $filepath
     * @return array
     */
    private function file_get_php_classes($filepath) {
        $php_code = file_get_contents($filepath);
        $classes = $this->get_php_classes($php_code);
        return $classes;
    }

    /**
     * Get a list of php classes in specified string
     * 
     * @param string $php_code
     * @return array
     */
    private function get_php_classes($php_code) {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ( $tokens[$i - 2][0] == T_CLASS && 
                    $tokens[$i - 1][0] == T_WHITESPACE && 
                    $tokens[$i][0] == T_STRING) {
                // Ignore abstract classes
                if( $tokens[$i - 4][0] == T_ABSTRACT ) {
                    continue;
                }
                $class_name = $tokens[$i][1];
                if( $currentNamespace ) {
                    $class_name = '\\' . $currentNamespace . '\\' . $class_name;
                }
                $classes[] = $class_name;
            } else if( $tokens[$i - 2][0] == T_NAMESPACE && 
                    $tokens[$i - 1][0] == T_WHITESPACE && 
                    $tokens[$i][0] == T_STRING ) {
                $j = $i;
                $currentNamespace = '';
                for( ; $j < $count; $j++ ) {
                    if( $tokens[$j][0] === T_STRING || 
                        $tokens[$j][0] === T_NS_SEPARATOR ) {
                        $currentNamespace .= $tokens[$j][1];
                    } else {
                        break;
                    }
                }
            }
        }
        return $classes;
    }

}
