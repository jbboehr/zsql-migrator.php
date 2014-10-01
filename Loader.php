<?php

namespace zsql\Migrator;

class Loader {
    
    static private $currentFile;
    
    static private $instance;
    
    static private $migrations;
    
    /**
     * @return \zsql\Migrator\Loader
     * @throws Exception
     */
    static public function _()
    {
        if( null === self::$instance ) {
            throw new Exception('No current migration loader instance');
        }
        return self::$instance;
    }
    
    public function __construct() {
        
    }
    
    public function loadFile($file) {
        if( isset(self::$migrations[$file]) ) {
            return $this->cloneMigrations(self::$migrations[$file]);
        }
        
        self::$instance = $this;
        self::$currentFile = $file;
        try {
            include_once $file;
            $classes = $this->file_get_php_classes($file);
            foreach( $classes as $class ) {
                if( is_subclass_of($class, '\\zsql\\Migrator\\MigrationInterface') ) {
                    $this->saveMigration(new $class());
                }
            }
            self::$currentFile = null;
            self::$instance = null;
        } catch( \Exception $e ) {
            self::$currentFile = null;
            self::$instance = null;
            throw $e;
        }
        return $this->cloneMigrations(self::$migrations[$file]);
    }
    

    public function migration() {
        $self = $this;
        $onSave = function(MigrationInterface $migration) use ($self) {
            $self->saveMigration($migration);
        };
        return new FluentMigration($onSave);
    }
    
    public function saveMigration(MigrationInterface $migration)
    {
        if( null === self::$currentFile ) {
            throw new Exception('No current migration file!');
        }
        $version = $migration->version();
        if( null === $version ) {
            throw new Exception('Migration must have a version');
        }
        if( isset(self::$migrations[self::$currentFile][$version]) ) {
            throw new Exception('Must not have migration with duplicate version!');
        }
        self::$migrations[self::$currentFile][$version] = $migration;
        return $this;
    }
    
    
    
    
    // Utilties
    
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
    
    private function file_get_php_classes($filepath) {
        $php_code = file_get_contents($filepath);
        $classes = $this->get_php_classes($php_code);
        return $classes;
    }

    private function get_php_classes($php_code) {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ( $tokens[$i - 2][0] == T_CLASS && 
                    $tokens[$i - 1][0] == T_WHITESPACE && 
                    $tokens[$i][0] == T_STRING) {
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
