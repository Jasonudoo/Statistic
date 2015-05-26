<?php
/**
 * File Interface
 * File could be any kind of resource, file, database, even socket
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Resource\Files;

interface FileInterface
{
    /**
     * read the file content
     * @return mixed
     */
    public function read();

    /**
     * write the content to the file
     * @param string $content the content to write
     * @return boolean
     */
    public function write($content);


    /**
     * open the file resource
     * @param mixed $mode, file open mode or database connection parameters
     * @return resource handle
     */
    public function open($mode);
}