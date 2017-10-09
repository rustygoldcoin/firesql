<?php

namespace Fire\Sql;

use PDO;
use DateTime;
use Fire\Sql\Statement;

class Collection
{

    /**
     * The connection to the database.
     * @var PDO
     */
    private $_pdo;

    /**
     * The name of the collection.
     * @var string
     */
    private $_name;

    /**
     * Creates an instance of a new collection.
     * @param String $name The name of the collection
     * @param PDO $pdo The connection to the database
     */
    public function __construct($name, PDO $pdo)
    {
        $this->_name = $name;
        $this->_pdo = $pdo;
        $statement = Statement::get(
            'CREATE_COLLECTION_TABLE',
            [
                '@collection' => $this->_pdo->quote($this->_name)
            ]
        );
        $this->_exec($statement);
    }

    public function delete($id)
    {

    }

    public function find($filter = null, $offset = 0, $length = 10, $reverseOrder = true)
    {

    }

    public function insert($object)
    {
        $this->_upsert($object, null);
    }

    public function update($id, $object)
    {

    }

    private function _addIndexes($object)
    {

    }

    private function _exec($statement)
    {
        $this->_pdo->exec($statement);
    }

    private function _generateRevisionNumber()
    {
        return rand(1000001, 9999999);
    }

    private function _generateTimestamp()
    {
        $time = microtime(true);
        $micro = sprintf('%06d', ($time - floor($time)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        return $date->format("Y-m-d H:i:s.u");
    }

    private function _generateUniqueId()
    {
        $rand = uniqid(rand(10, 99));
        $time = microtime(true);
        $micro = sprintf('%06d', ($time - floor($time)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        $id = $date->format('YmdHisu') . $rand;
        return $id;
    }

    private function _getObject($id)
    {

    }

    private function _objectExists($id)
    {
        return false;
    }

    private function _query($statement)
    {
        
    }

    private function _removeIndexes($object)
    {

    }

    private function _updateObjectRevision($id, $revision)
    {

    }

    private function _upsert($object, $id = null)
    {
        $object = $this->_writeObjectToDb($object, $id);
        if ($this->_objectExists($object->__id)) {
            $prevObject = $this->_getObject($object->__id);
            $this->_removeIndexes($prevObject);
        }
        $this->_addIndexes($object);
        $this->_updateObjectRevision($object->__id, $object->__revision);
        return $object;
    }

    private function _writeObjectToDb($object, $id)
    {
        $objectId = (!is_null($id)) ? $id : $this->_generateUniqueId();
        $object->__id = $objectId;
        $object->__revision = $this->_generateRevisionNumber();
        $object->__timestamp = $this->_generateTimestamp();

        //insert into database
        $statement = Statement::get(
            'INSERT_OBJECT_INTO_COLLECTION',
            [
                '@collection' => $this->_pdo->quote($this->_name),
                '@id' => $this->_pdo->quote($object->__id),
                '@revision' => $this->_pdo->quote($object->__revision),
                '@created' => $this->_pdo->quote($object->__timestamp),
                '@obj' => $this->_pdo->quote(json_encode($object))
            ]
        );
        $this->_exec($statement);

        return $object;
    }
}
