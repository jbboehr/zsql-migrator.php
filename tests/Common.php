<?php

class Common_Test extends PHPUnit_Framework_TestCase
{
  protected $fixtureOneRowCount = 2;
  
  protected $useVerboseErrorHandler = false;
  
  public function __construct($name = NULL, array $data = array(), $dataName = '')
  {
    parent::__construct($name, $data, $dataName);
    
    if( $this->useVerboseErrorHandler ) {
      $this->setVerboseErrorHandler();
    }
  }
  
  protected function setVerboseErrorHandler() 
  {
    $handler = function($errorNumber, $errorString, $errorFile, $errorLine) {
        echo "ERROR INFO\nMessage: $errorString\nFile: $errorFile\nLine: $errorLine\n";
    };
    set_error_handler($handler);        
  }
  
  protected function fixtureModelOneFactory()
  {
    return new FixtureModelOne($this->databaseFactory());
  }
  
  protected function databaseFactory()
  {
    $mysql = new \mysqli();
    $mysql->connect('localhost', 'zsql', 'nopass', 'zsql');
    return new \zsql\Database($mysql);
  }
  
  public function getReflectedPropertyValue($class, $propertyName)
  {
    $reflectedClass = new ReflectionClass($class);
    $property = $reflectedClass->getProperty($propertyName);
    $property->setAccessible(true);
 
    return $property->getValue($class);
  }
  
  public function setReflectedPropertyValue($object, $propertyName, $value)
  {
    $reflectedClass = new ReflectionObject($object);
    $property = $reflectedClass->getProperty($propertyName);
    $property->setAccessible(true);
    $property->setValue($object, $value);  
  }
  
  public function callReflectedMethod($object, $method)
  {
    $args = array_slice(func_get_args(), 2);
    $reflector = new ReflectionObject($object);
    $mr = $reflector->getMethod($method);
    $mr->setAccessible(true);
    try {
        return $mr->invokeArgs($object, $args);
    } catch( \Exception $e ) {
        $mr->setAccessible(false);
        throw $e;
    }
  }
}

class Common_Query_Test extends Common_Test
{
  protected $_className;
  
  public function testClassExists()
  {
    $this->assertEquals(class_exists($this->_className), true);
  }
  
  public function testConstruction()
  {
    $this->assertInstanceOf($this->_className, $this->_factory());
  }
  
  public function testMagicToString_Fails()
  {
    $reporting = error_reporting(0);
    $this->assertEmpty((string) $this->_factory());
    error_reporting($reporting);
    $lastError = error_get_last();
    $this->assertEquals('No table specified', $lastError['message']);
  }
  
  public function testParts()
  {
    $this->assertEquals(true, is_array($this->_factory()->parts()));
  }
  
  public function testParams()
  {
    $this->assertEquals(true, is_array($this->_factory()->params()));
  }
  
  public function testQuery_ThrowsException()
  {
    $query = $this->_factory();
    $exception = null;
    try {
      $query->query();
    } catch( Exception $e ) {
      $exception = $e;
    }
    $this->assertInstanceOf('\\zsql\\Exception', $exception);
  }
  
  public function testSetQuoteCallback_ThrowsException()
  {
    $query = $this->_factory();
    $exception = null;
    try {
      $query->setQuoteCallback(false);
    } catch( Exception $e ) {
      $exception = $e;
    }
    $this->assertInstanceOf('\\zsql\\Exception', $exception);
  }
  
  public function testInvalidConstructionArgThrowsException()
  {
    $exception = null;
    try {
      $query = new $this->_className('blah');
    } catch( Exception $e ) {
      $exception = $e;
    }
    $this->assertInstanceOf('\\zsql\\Exception', $exception);
  }
  
  protected function _factory()
  {
    return new $this->_className();
  }
  
  protected function _getQuoteCallback()
  {
    // This is not a real quote function, just for testing
    return function($string) {
      if( is_int($string) || is_double($string) ) {
        return $string;
      } else if( is_null($string) ) {
        return 'NULL';
      } else if( is_bool($string) ) {
        return $string ? 'TRUE' : 'FALSE';
      } else {
        return "'" . addslashes($string) . "'";
      }
    };
  }
}

class Common_Migrator_Test extends Common_Test
{
    public function migratorFactory($file, $table = null)
    {
        if( $table === null ) {
            $table = 'migrations';
        }
        return new zsql\Migrator\Migrator(array(
            'database' => $this->databaseFactory(),
            'migrationPath' => __DIR__ . '/fixtures/' . $file . '.php',
            'migrationTable' => $table,
        ));
    }
    
    public function commandFactory($args, $file, $table = null)
    {
        $migrator = $this->migratorFactory($file, $table);
        return new \zsql\Migrator\Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $migrator,
        ), $args);
    }
}

class FixtureMigrationInvalidName extends zsql\Migrator\LegacyMigration {
    public function down() {
        
    }

    public function up() {
        
    }

}

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
