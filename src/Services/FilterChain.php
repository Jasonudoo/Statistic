<?php
/**
 * Filter Chains
 *
 * @since: April 14, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics\Filters;

use LogAnalytics\Exception\InvalidArgumentException;
use LogAnalytics\FilterInterface;
use LogAnalytics\Helpers\ErrorCode;
use LogAnalytics\Files\Data\HttpLogData;
use LogAnalytics\Helpers\Queue;

class FilterChain
{
    /**
     * Default priority at which filters are added
     */
    const DEFAULT_PRIORITY = 1000;

    /**
     * Array to save the filter
     *
     * @var array
     */
    protected $filters;

    /**
     * The queue to save the filter
     *
     *  @var \LogAnalytics\Helpers\Queue
     */
    private $queue;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filters = array();
        $this->queue = new Queue();
    }

    /**
     * Attach the filter to the Filter Queue
     * @param mixed $callback
     * @param string $parameter
     * @param int $priority
     * @throws InvalidArgumentException
     * @return \LogAnalytics\Filters\FilterChain
     */
    public function attach($callback, $parameter = NULL, $priority = self::DEFAULT_PRIORITY)
    {
        if (!is_callable($callback)) {
            if (!$callback instanceof FilterInterface) {
                $code = ErrorCode::ERROR_FILTER_CHAIN_CALLBACK_INVALIDATED;
                $message = sprintf(ErrorCode::getMessage($code), (is_object($callback) ? get_class($callback) : gettype($callback)) );
                throw new InvalidArgumentException($message, $code);
            }
            $callback = array($callback, 'filter');
        }
        $this->insert($callback, $parameter, $priority);

        return $this;
    }

    /**
     * Attache to the filter queue by filter name
     * @param string $filter
     * @param mixed $parameter
     * @param array $option
     * @param integer $priority
     * @return \LogAnalytics\Filters\FilterChain
     */
    public function attachByName($filter, $parameter = NULL, $option = NULL, $priority = self::DEFAULT_PRIORITY)
    {
        $className = "\\LogAnalytics\\Filters\\" . $filter;

        $filterInstance = new $className;
        if( !is_null($option) ) {
            foreach($option as $key => $value) {
                $setter = "set" . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                if( method_exists($filterInstance, $setter) ) {
                    $filterInstance->{$setter}($value);
                }
            }
        }

        $callback = array($filterInstance, "filter");

        return $this->attach($callback, $parameter, $priority);
    }

    /**
     * Insert the filter to the internal queue
     * @param mixed $callback
     * @param mixed $parameter
     * @param int $priority
     */
    private function insert($callback, $parameter = NULL, $priority = self::DEFAULT_PRIORITY)
    {
        $priority = (int) $priority;
        $this->filters[] = array(
            'callback' => $callback,
            'parameter'=> $parameter,
            'priority' => $priority,
        );

        $this->getQueue()->insert($callback, $priority);

    }

    /**
     * Get the internel filter queue
     *
     * @return \LogAnalytics\Helpers\Queue
     */
    public function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new Queue();
        }
        return $this->queue;
    }

    /**
     * Returns $value filtered through each filter in the chain
     *
     * Filters are run in the order in which they were added to the chain (FIFO)
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        $chain = $this->filters;
        if( FALSE === is_array($value) ) {
            $value = array($value);
        }

        $valueFiltered = NULL;
        foreach($chain as $filter) {
            if(!isset($filter['callback']) || empty($filter['callback']) ) continue;
            if( !is_callable($filter['callback']) ) continue;

            for($i = 0; $i < sizeof($value); $i++ ){
                $data = $value[$i];

                if( isset($filter['parameter']) && !is_null($filter['parameter']) ) {
                    if( is_array($filter['parameter']) ) {
                        $valueFiltered = $this->getArrayValue($value, $i, $filter['parameter']);
                    } else {
                        $valueFiltered = $this->getStringValue($data, $filter['parameter']);
                    }
                }

                var_dump($valueFiltered);

                $callFilter = $filter['callback'];
                $result = call_user_func($callFilter, $valueFiltered);
                if($result) {
                    unset($value[$i]);
                }
            }
            $value = array_values($value);
        }

        return $value;
    }

    private function getStringValue($data, $parameter)
    {
        $dataKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $parameter)));
        if( is_object($data) ) {
            $getter = 'get' . $dataKey;
            if(method_exists($data, $getter)){
                $valueFiltered = $data->{$getter}();
            }
        } elseif ( is_array($data) ) {
            if( isset($data[$dataKey]) ) {
                $valueFiltered = $data[$dataKey];
            }
        } else {
            $valueFiltered = $data;
        }

        return $valueFiltered;

    }

    private function getArrayValue($dts, $index, $parameters)
    {
        $valueFiltered = array();
        for($i = 0; $i < sizeof($parameters); $i++) {
            $parameter = $parameters[$i];
            if( is_string($parameter) ) {
                $data = $dts[$index];
                $valueFiltered[$i] = $this->getStringValue($data, $parameter);
            } else {
                $next_index = $index + $i;
                if($next_index >= sizeof($dts) ) {
                    $data = NULL;
                } else {
                    $data = $dts[$next_index];
                }
                $valueFiltered[$i] = $this->getArrayParameterValue($data, $parameter);
            }
        }

        return $valueFiltered;
    }

    private function getArrayParameterValue($data, $parameters)
    {
        if( is_null($data) ) return NULL;

        $valueFiltered = array();

        foreach($parameters as $parameter) {
            $dataKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $parameter)));
            if( is_array($parameter) ) {
                $valueFiltered[$dataKey] = $this->getArrayParameterValue($data, $parameter);
            } else {
                $valueFiltered[$dataKey] = $this->getStringValue($data, $parameter);
            }
        }
        return $valueFiltered;
    }
}