<?php
/**
 * MongoDB Operation Class
 *
 * @since: May 22, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Resource\Database;

use Statistic\Service\Exception\RuntimeException;
use Statistic\Service\Helpers\Common;
use Statistic\Service\Helpers\ErrorCode;
use Statistic\Service\Helpers\Weblog;

class Mongodb
{
    const MONGO_SOCKET_TIMEOUT = 500;

    private static $MongoAllowOptions = array(
        'w',
        'upsert',
        'multiple',
        'justOne',
        'fsync',
        'j',
        'socketTimeoutMS',
        'wTimeoutMS',
        'hint',
        'limit',
        'skip',
    );
    private static $MongoDeprecatedOptions = array('safe', 'timeout', 'wtimeout');
    private static $MongoAliasOptions = array(
        'safe' => 'w',
        'timeout' => 'socketTimeoutMS',
        'wtimeout' => 'wTimeoutMS'
    );

    private static $MongoOptionsDefaultValue = array(
        'w' => 1,
        'justOne' => TRUE,
        'fsync' => FALSE,
        'j' => FALSE,
        'socketTimeoutMS' => self::MONGO_SOCKET_TIMEOUT,
    );

    /**
     * The MongoClient Object Instance of writable
     * @var Array | \MongoClient
     */
    private static $writeHandle = null;

    /**
     * The server connection params for reconnection
     * @var array
     */
    private static $serverParams = array();

    /**
     * The MongoDB Instance to Write
     * @var MongoDB
     */
    private $writeDBHandle = null;

    /**
     * Database Name
     * @var String
     */
    private $dbname = null;

    // Constructor
    public function __construct()
    {
    }

    // Destructor
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connect to the MongoDB according to the Server Parameter Passed
     * Single Server: the server parameters should be dsn string or array like that
     * array('user' => 'username',
     *       'password' => 'password',
     *       'host' => 'mongodb server host',
     *       'port' => 'mongodb server port',
     *       'dbname' => 'mongodb database name'
     * );
     * Master - Slave Server: the server parameters should be like
     * array('master' => array(), 'slave' => array());
     * @param string|array $server
     */
    public function connect($server)
    {
        if( !is_array($server) ) {
            $server = array($server);
        }

        self::$serverParams = $server;
        $this->setServerConnection($server);
    }

    /**
     * try to connect the mongo database again
     * @return bool
     */
    private function reconnect()
    {
        if( is_null(self::$serverParams) ) {
            return FALSE;
        }
        $server = self::$serverParams;
        $this->connect($server);
        return TRUE;
    }

    /**
     * Single Mongo Server Connection
     * @param array $server
     * @throws RuntimeException
     * @return boolean
     */
    private function setServerConnection($server = array())
    {
        //since it is single server connection
        if( sizeof($server) > 1) {
            $dsn = $this->generateDsn($server);
        } else {
            $dsn = array_shift($server);
        }

        if( is_null(self::$writeHandle) || !(self::$writeHandle instanceof \MongoClient) ) {
            try{
                $handle = new \MongoClient($dsn);
                self::$writeHandle = $handle;
                $this->writeDBHandle = $handle->selectDB($this->dbname);
                unset($handle);
            } catch (\MongoConnectionException $e) {
                $this->log('CONNECTION', $this->dbname, $e->getMessage(), $e->getCode(), Common::serializeObject($dsn) );
                throw new RuntimeException($e->getMessage(), $e->getCode());
            }
        }
        return TRUE;
    }

    /**
     * Generate the DSN connection string
     * @param array $server
     * @throws RuntimeException
     * @return string
     */
    private function generateDsn($server)
    {
        if( isset($server['user']) && !empty($server['user']) ) {
            $user = $server['user'];
        } else {
            $code = ErrorCode::ERROR_MONGODB_USERNAME_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new RuntimeException($message, $code);
        }

        if( isset($server['password']) ) {
            $passwd = $server['password'];
        } else {
            $code = ErrorCode::ERROR_MONGODB_PASSWORD_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new RuntimeException($message, $code);
        }

        if( isset($server['port']) && is_int($server['port'] - 0) ) {
            $port = $server['port'];
        } else {
            $port = "27017";
        }

        if( !isset($server['host']) ) {
            $host = "localhost";
        } else {
            $host = $server['host'];
        }

        if( !isset($server['dbname']) ) {
            $code = ErrorCode::ERROR_MONGODB_DBNAME_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new RuntimeException($message, $code);
        } else {
            $dbname = $server['dbname'];
        }
        //mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/db
        $dsn = "mongodb://" . $user . ":" . $passwd . "@" . $host . ":" . $port . "/" . $dbname;
        $this->dbname = $dbname;

        return $dsn;
    }

    /**
     * Select Database
     * @param string $dbname
     * @throws RuntimeException
     * @return \Statistic\Service\Resource\Mongodb
     */
    public function selectDB($dbname = null)
    {
        if( !is_null($dbname) && !empty($dbname) ) {
            $this->dbname = $dbname;
            try{
                $this->writeDBHandle = self::$writeHandle->selectDB($this->dbname);
            } catch (\Exception $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode());
            }
        }
        return $this;
    }

    /**
     * Create the Index
     * @param string $table
     * @param string $index
     * @param array $options
     * @throws RuntimeException
     * @return boolean
     */
    public function createIndex($table, $index, $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $flags = $this->checkAllowedOptions($options);

        try{
            $mongo->selectCollection($table)->createIndex($index, $flags);
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return TRUE;
    }

    /**
     * Insert document object to collection
     * @param string $table
     * @param object $record
     * @param array $options
     * @throws RuntimeException
     * @return boolean
     */
    public function insert($table, $record, $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $flags = $this->checkAllowedOptions($options);

        try{
            $result = $mongo->selectCollection($table)->insert($record, $flags);
        } catch (\MongoCursorTimeoutException $e ) {
            $this->log('INSERT', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($record) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoCursorException $e ) {
            $this->log('INSERT', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($record) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoException $e ) {
            $this->log('INSERT', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($record) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        if( is_array($result) ) {
            if( intval($result['ok']) !== 1) {
                $error_code = isset($result['code']) ? $result['code'] : 9999;
                $this->log('INSERT', $table, $result['err'] . ' ' . $result['errmsg'], $error_code, Common::serializeObject($record) );
                throw new RuntimeException($result['err'] . ' ' . $result['errmsg'], $error_code);
            } else {
                return TRUE;
            }
        }

        return TRUE;
    }

    /**
     * Update the document in a collection
     * @param string $table
     * @param array $condition
     * @param array $newdata
     * @param array $options
     * @throws RuntimeException
     * @return boolean|NULL|number
     * TODO: Catch the error code 10054 and 16 try to reconnect to the master
     */
    public function update($table, $condition, $newdata, $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $flags = $this->checkAllowedOptions($options);

        try{
            $result = $mongo->selectCollection($table)->update($condition, $newdata, $flags);

        } catch (\MongoCursorTimeoutException $e) {
            $this->log('UPDATE', $table, $e->getMessage(), $e->getCode(), Common::serializeObject(array('CRITERIA' => $condition, 'DATA' => $newdata)) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoCursorException $e) {
            $this->log('UPDATE', $table, $e->getMessage(), $e->getCode(), Common::serializeObject(array('CRITERIA' => $condition, 'DATA' => $newdata)) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        if( is_array($result) ) {
            if( intval($result['ok']) !== 1) {
                $error_code = isset($result['code']) ? $result['code'] : 9999;
                $this->log('UPDATE', $table, $result['err'] . ' ' . $result['errmsg'], $error_code, Common::serializeObject(array('CRITERIA' => $condition, 'DATA' => $newdata)) );
                throw new RuntimeException($result['err'] . ' ' . $result['errmsg'], $error_code);
            } else {
                return $result['n'];
            }
        }
        return $result;
    }

    public function findAndModify($table, $newdata, $condition, $fields = "", $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $fields = $this->fieldStrToArray($fields);

        try{
            $result = $mongo->selectCollection($table)->findAndModify($condition, $newdata, $fields, $options);
        } catch (\MongoResultException $e) {
            $this->log('FIND_AND_MODIFY', $table, $e->getMessage(), $e->getCode(), Common::serializeObject(array('CRITERIA' => $condition, 'DATA' => $newdata)) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $result;
    }


    /**
     * Remove the records from the collection based on the condition
     * @param string $table
     * @param array $condition
     * @param array $options
     * @throws RuntimeException
     * @return boolean|NULL|number
     * TODO: Catch the error code 10054 and 16 try to reconnect to the master
     */
    public function remove($table, $condition, $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $flags = $this->checkAllowedOptions($options);

        try{
            $result = $mongo->selectCollection($table)->remove($condition, $flags);
        } catch (\MongoCursorTimeoutException $e) {
            $this->log('REMOVE', $table, $e->getMessage(), $e->getCode(),  Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoCursorException $e) {
            $this->log('REMOVE', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        if( is_array($result) ) {
            if( intval($result['ok']) !== 1) {
                $error_code = isset($result['code']) ? $result['code'] : 9999;
                $this->log('REMOVE', $table, $result['err'] . ' ' . $result['errmsg'], $error_code, Common::serializeObject($condition) );
                throw new RuntimeException($result['err'] . ' ' . $result['errmsg'], $error_code);
            } else {
                return $result['n'];
            }
        }

        return $result;
    }

    /**
     * According to the condition to get the collection records count number
     * @param string $table
     * @param array $condition
     * @param array $options
     * @throws RuntimeException
     * @return boolean|unknown
     */
    public function count($table, $condition = array(), $options = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        try{
            $result = $mongo->selectCollection($table)->count($condition);
        } catch (\MongoResultException $e) {
            $this->log('COUNT', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoExecutionTimeoutException $e) {
            $this->log('COUNT', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * Queries this collection, returning a MongoCursor for the result set
     * @param string $table
     * @param string $fields
     * @param array $condition
     * @param array $sort
     * @param number $limit
     * @throws RuntimeException
     * @return \MongoCursor
     */
    public function find($table, $fields ="" , $condition = array(), $sort = array(), $limit = 0)
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $fields = $this->fieldStrToArray($fields);

        try{
            $cursor = $mongo->selectCollection($table)->find($condition, $fields)->timeout(self::MONGO_SOCKET_TIMEOUT);
            if(is_array($sort) && !empty($sort)) $cursor->sort($sort);
            if(is_int($limit) && $limit > 0)$cursor->limit($limit);
        } catch (\MongoException $e) {
            $this->log('FIND', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $cursor;

    }

    /**
     * Advances the cursor to the next result, and returns that result
     * @param \MongoCursor $cursor
     * @throws RuntimeException
     * @return boolean|array
     */
    public function next(\MongoCursor $cursor)
    {
        if( !$cursor->hasNext() ) return FALSE;

        try{
            $result = $cursor->getNext();
        } catch (\MongoCursorException $e) {
            $cursorInfo = $cursor->info();
            $info = Common::serializeObject($cursorInfo);
            $this->log('GetNext', $cursorInfo['ns'], $e->getMessage(), $e->getCode(), $info );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (\MongoException $e) {
            $cursorInfo = $cursor->info();
            $info = Common::serializeObject($cursorInfo);
            $this->log('GetNext', $cursorInfo['ns'], $e->getMessage(), $e->getCode(), $info );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * Return only the first result from the result set
     * @param string $table
     * @param string $fields
     * @param array $condition
     * @throws RuntimeException
     * @return boolean|array
     */
    public function findOne($table, $fields = "", $condition = array())
    {
        if( is_null($this->writeDBHandle) ) return FALSE;
        $mongo = $this->writeDBHandle;

        $fields = $this->fieldStrToArray($fields);

        try{
            $result = $mongo->selectCollection($table)->findOne($condition, $fields);
        } catch(\MongoException $e) {
            $this->log('FINDONE', $table, $e->getMessage(), $e->getCode(), Common::serializeObject($condition) );
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * Convert a comma separated list of fields into the array needed by find() or findOne()
     * @param string $fields
     * @return multitype:boolean
     */
    private function fieldStrToArray($fields) {
        $out = array();

        if( !empty($fields) && is_string($fields) ) {
            $tmp = explode(",",$fields);
            foreach($tmp as $f=>$k) {
                $k = trim($k);
                if(!empty($k)) $out[$k] = true; //array('fieldname' => true)
            }
        }
        return $out;
    }

    private function close()
    {

    }

    /**
     * Check the Mongo Allowed Flags and set the default option value
     * @param array $options
     * @return array $options;
     * TODO: add the Insert/Update/Delete Optimizer Options
     */
    private function checkAllowedOptions($options)
    {
        foreach($options as $key => $value) {
            if( !in_array($key, self::$MongoAllowOptions) && !in_array($key, self::$MongoDeprecatedOptions) ) {
                unset($options[$key]);
            }

            if( in_array($key, self::$MongoDeprecatedOptions) ) {
                $key = self::$MongoAliasOptions[$key];
                if( !isset($options[$key]) ) {
                    $options[$key] = $value;
                }
            }
        }

        if( !isset($options['socketTimeoutMS']) ) {
            $options['socketTimeoutMS'] = self::$MongoOptionsDefaultValue['socketTimeoutMS'];
        }

        if( !isset($options['w']) ) {
            $options['w'] = self::$MongoOptionsDefaultValue['w'];
        }
        reset($options);

        return $options;
    }

    private function log($type, $db, $error, $code, $info = '')
    {
        //"Operation now in progress" is code for timeout
        if(stristr($error,"Operation now in progress")) $error = "Timeout ($error)";

        $message = " MONGO ". \MongoClient::VERSION . " " . $type . " ERROR " . $code . " \"" . $error .
            "\" AT '" . __FILE__ . "' " . $db . " " . $info;
        Weblog::write(Weblog::WEBLOG_ERROR, $message);
    }
}