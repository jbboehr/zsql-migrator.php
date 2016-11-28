<?php

namespace zsql\Migrator;

use zsql\Adapter;

/**
 * Abstract migration class
 */
abstract class MigrationAbstract implements MigrationInterface
{
    use Adapter\AdapterAwareTrait;

    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $state;
    
    /**
     * @var integer
     */
    protected $version;

    /**
     * Get or set the migration name
     * 
     * @param string $name
     * @return mixed
     */
    public function name($name = null)
    {
        if( null === $name ) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get or set the migration state
     * 
     * @param string $state
     * @return mixed
     */
    public function state($state = null)
    {
        if( null === $state ) {
            return ($this->state ?: 'initial');
        }
        $this->state = $state;
        return $this;
    }
    
    /**
     * Get or set the migration version
     * 
     * @param integer $version
     * @return mixed
     */
    public function version($version = null)
    {
        if( null === $version ) {
            return $this->version;
        }
        $this->version = $version;
        return $this;
    }
    
    public function __toString()
    {
        return 'Migration:'
             . ' version=' . $this->version
             . ' name=' . $this->name
             . ' state=' . $this->state;
    }
}
