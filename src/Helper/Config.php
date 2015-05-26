<?php
/**
 * Render the config array as object
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Helpers;

/**
 * Provides a property based interface to an array.
 */
class Config
{
    /**
     * Number of elements in configuration data.
     *
     * @var int
     */
    protected $count;

    /**
     * Data within the configuration.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Constructor.
     *
     * @param  array   $array
     */
    public function __construct(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($value);
            } else {
                $this->data[$key] = $value;
            }

            $this->count++;
        }
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set a value in the config.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        if (is_array($value)) {
            $value = new static($value, true);
        }

        if (null === $name) {
            $this->data[] = $value;
        } else {
            $this->data[$name] = $value;
        }

        $this->count++;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data  = $this->data;

        /** @var self $value */
        foreach ($data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * isset() overloading
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->count--;
        }
    }
}
