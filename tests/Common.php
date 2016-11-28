<?php

namespace zsql\Migrator\Tests;

use ReflectionClass;
use ReflectionObject;
use zsql\Adapter\MysqliAdapter;
use zsql\Migrator\Command;
use zsql\Migrator\Migrator;

class Common extends \PHPUnit_Framework_TestCase
{
    protected $fixtureOneRowCount = 2;

    protected $useVerboseErrorHandler = false;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if ($this->useVerboseErrorHandler) {
            $this->setVerboseErrorHandler();
        }
    }

    protected function setVerboseErrorHandler()
    {
        $handler = function ($errorNumber, $errorString, $errorFile, $errorLine) {
            echo "ERROR INFO\nMessage: $errorString\nFile: $errorFile\nLine: $errorLine\n";
        };
        set_error_handler($handler);
    }

    protected function databaseFactory()
    {
        $mysql = new \mysqli();
        $mysql->connect('127.0.0.1', 'zsql', 'nopass', 'zsql');
        return new MysqliAdapter($mysql);
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
        } catch (\Exception $e) {
            $mr->setAccessible(false);
            throw $e;
        }
    }

    /**
     * @param $file
     * @param null $table
     * @return Migrator
     */
    public function migratorFactory($file, $table = null)
    {
        if ($table === null) {
            $table = 'migrations';
        }
        return new Migrator(array(
            'database' => $this->databaseFactory(),
            'migrationPath' => __DIR__ . '/fixtures/' . $file . '.php',
            'migrationTable' => $table,
        ));
    }

    public function commandFactory($args, $file, $table = null)
    {
        $migrator = $this->migratorFactory($file, $table);
        return new Command(array(
            'database' => $this->databaseFactory(),
            'migrator' => $migrator,
        ), $args);
    }
}
