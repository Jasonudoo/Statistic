<?php
/**
 * Simple Queue Class
 *
 * @since: April 14, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics\Helpers;

class Queue
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Inner queue object
     * @var SplPriorityQueue
    */
    protected $queue;

    /**
     * Insert an item into the queue
     *
     * Priority defaults to 1 (low priority) if none provided.
     *
     * @param  mixed $data
     * @param  int $priority
     * @return Queue
     */
    public function insert($data, $priority = 1)
    {
        if (!is_array($priority)) {
            $priority = array($priority, $this->serial--);
        }

        $this->getQueue()->insert($data, $priority);
        return $this;
    }

    /**
     * Get the inner priority queue instance
     *
     * @return SplPriorityQueue
     */
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new \SplPriorityQueue();
        }
        return $this->queue;
    }
}