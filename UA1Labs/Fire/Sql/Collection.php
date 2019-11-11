<?php
/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireSql
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

namespace Fire\Sql;

use \DateTime;
use \Fire\Sql\Statement;
use \Fire\Sql\Filter;
use \Fire\Sql\Connector;

/**
 * The class that represents a collection. With the functionality built into this class
 * you'll be able to create, read, update, and delete objects from within the collection.
 */
class Collection
{

    /**
     * The connection to the database.
     * @var \Fire\Sql\Connector
     */
    private $_connector;

    /**
     * The name of the collection.
     * @var string
     */
    private $_name;

    /**
     * Options used to configure how a collection
     * should work.
     * @var object
     */
    private $_options;

    /**
     * Creates an instance of a new collection.
     *
     * Default $options:
     * versionTracking | false | Determines if object updates should maintain the history.
     * @param string $name The name of the collection
     * @param PDO $pdo The connection to the database
     * @param array $options An array of options
     */
    public function __construct($name, Connector $connector, $options = null)
    {
        $this->_connector = $connector;
        $this->_name = $name;

        $defaultOptions = [
            'versionTracking' => false
        ];

        if ($options) {
            $opts = [];
            foreach ($defaultOptions as $option => $value) {
                $opts[$option] = isset($options[$option]) ? $options[$option] : $defaultOptions[$option];
            }
            $this->_options = (object) $opts;
        } else {
            $this->_options = (object) $defaultOptions;
        }

        $createTables = Statement::get('CREATE_DB_TABLES', [
            '@collection' => $this->_name
        ]);
        $this->_connector->exec($createTables);
    }

    /**
     * Returns a collection of objects that match the filter criteria
     * @param string|null|Fire\Sql\Filter $filter
     * @return void
     */
    public function find($filter = null)
    {
        if (is_string($filter)) {
            json_decode($filter);
            $isJson = (json_last_error() === JSON_ERROR_NONE) ? true :false;
            if ($isJson) {
                $filter = new Filter($filter);
                return $this->_getObjectsByFilter($filter);
            } else {
                return $this->_getObject($filter);
            }
        } else if (is_object($filter) && $filter instanceof Filter) {
            return $this->_getObjectsByFilter($filter);
        }
        return [];
    }

    /**
     * Inserts an object in the collection.
     * @param object $object
     * @return void
     */
    public function insert($object)
    {
        return $this->_upsert($object, null);
    }

    /**
     * Updates and object in the collection.
     * @param string $id
     * @param object $object
     * @return void
     */
    public function update($id, $object)
    {
        return $this->_upsert($object, $id);
    }

    /**
     * Deletes an object from the database.
     * @param string $id The ID of the object you want to delete
     * @return void
     */
    public function delete($id)
    {
        $delete = Statement::get(
            'DELETE_OBJECT_INDEX',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($id)
            ]
        );
        $delete .= Statement::get(
            'DELETE_OBJECT',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($id)
            ]
        );

        $this->_connector->exec($delete);
    }

    /**
     * Returns the total number of objects in a collection.
     * @param string|null|Fire\Sql\Filter $filter
     * @return int
     */
    public function count($filter = null)
    {
        if (is_null($filter)) {
            return $this->_countObjectsInCollection();
        } else if (is_string($filter)) {
            json_decode($filter);
            $isJson = (json_last_error() === JSON_ERROR_NONE) ? true :false;
            if ($isJson) {
                $filter = new Filter($filter);
                return $this->_countObjectsInCollectionByFilter($filter);
            }
        } else if (is_object($filter) && $filter instanceof Filter) {
            return $this->_countObjectsInCollectionByFilter($filter);
        }
        return 0;
    }

    /**
     * After an object has been fully indexed, the object needs to be updated to
     * indicated it is ready to be used within the collection.
     * @param string $id
     * @param int $revision
     * @return void
     */
    private function _commitObject($id, $revision)
    {
        $update = '';
        //if version tracking is disabled, delete previous revisions of the object
        if (!$this->_options->versionTracking) {
            $update .= Statement::get(
                'DELETE_OBJECT_EXCEPT_REVISION',
                [
                    '@collection' => $this->_name,
                    '@id' => $this->_connector->quote($id),
                    '@revision' => $this->_connector->quote($revision)
                ]
            );
        }
        $update .= Statement::get(
            'UPDATE_OBJECT_TO_COMMITTED',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($id),
                '@revision' => $this->_connector->quote($revision)
            ]
        );

        $this->_connector->exec($update);
    }

    /**
     * This method is used to dynamically create tokens to help manage
     * temporary tables within SQL queries.
     * @return string A randomly genereated token
     */
    private function _generateAlphaToken()
    {
        $timestamp = strtotime($this->_generateTimestamp());
        $characters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        shuffle($characters);
        $randomString = '';
        foreach (str_split((string) $timestamp) as $num) {
            $randomString .= $characters[$num];
        }
        return $randomString;
    }

    /**
     * This method creates a random revision number stamp.
     * @return int
     */
    private function _generateRevisionNumber()
    {
        return rand(1000001, 9999999);
    }

    /**
     * Generates a timestamp with micro seconds.
     * @return string
     */
    private function _generateTimestamp()
    {
        $time = microtime(true);
        $micro = sprintf('%06d', ($time - floor($time)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        return $date->format("Y-m-d H:i:s.u");
    }

    /**
     * Generates a unique id based on a timestamp so that it is truely unique.
     * @return string
     */
    private function _generateUniqueId()
    {
        $rand = uniqid(rand(10, 99));
        $time = microtime(true);
        $micro = sprintf('%06d', ($time - floor($time)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        return sha1($date->format('YmdHisu'));
    }

    /**
     * Returns an object in the collection. If the revision isn't provided, the
     * latest revision of the object will be returned.
     * @param string $id
     * @param int $revision
     * @return object|null
     */
    private function _getObject($id, $revision = null)
    {
        if ($revision === null) {
            $select = Statement::get(
                'GET_CURRENT_OBJECT',
                [
                    '@collection' => $this->_name,
                    '@id' => $this->_connector->quote($id)
                ]
            );
            $record = $this->_connector->query($select)->fetch();
            if ($record) {
                return json_decode($record['obj']);
            }
        }
        return null;
    }

    /**
     * Returns an object's origin timestamp date.
     * @param string $id
     * @return string|null
     */
    private function _getObjectOrigin($id)
    {
        $select = Statement::get(
            'GET_OBJECT_ORIGIN_DATE',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($id)
            ]
        );
        $record = $this->_connector->query($select)->fetch();
        return ($record) ? $record['updated'] : null;
    }

    /**
     * Returns a collection of objects that matches a filter.
     * @param Filter $filterQuery
     * @return array
     */
    private function _getObjectsByFilter(Filter $filterQuery)
    {
        $records = [];
        $props = [];
        $filters = [];
        foreach ($filterQuery->getComparisons() as $comparison) {
            $props[] = $comparison->prop;
            if ($comparison->expression && $comparison->comparison && $comparison->prop) {
                $expression = ($comparison->expression !== 'WHERE') ? $comparison->expression . ' ' : '';
                $prop = is_int($comparison->val) ? 'CAST(' . $comparison->prop . ' AS INT)' : $comparison->prop;
                $compare = $comparison->comparison;
                $value = (!isset($comparison->val) || is_null($comparison->val)) ? 'NULL' : $comparison->val;
                $filters[] = $expression . $prop . ' ' . $compare . ' \'' . $value . '\'';
            }
        }

        $props = array_unique($props);
        $joins = [];
        foreach ($props as $prop)
        {
            $standardFields = [
                '__id',
                '__type',
                '__collection',
                '__origin'
            ];
            if (!in_array($prop, $standardFields)) {
                $asTbl = $this->_generateAlphaToken();
                $joins[] =
                    'JOIN(' .
                        'SELECT id, val AS ' . $prop . ' ' .
                        'FROM ' . $this->_name . '__index ' .
                        'WHERE prop = \'' . $prop . '\'' .
                    ') AS ' . $asTbl . ' ' .
                    'ON A.id = ' . $asTbl . '.id';
            }
        }

        $select = Statement::get(
            'GET_OBJECTS_BY_FILTER',
            [
                '@collection' => $this->_name,
                '@columns' => (count($props) > 0) ? ', ' . implode($props, ', ') : '',
                '@joinColumns' => (count($joins) > 0) ? implode($joins, ' ') . ' ' : '',
                '@type' => $this->_connector->quote($filterQuery->getIndexType()),
                '@filters' => ($filters) ? 'AND (' . implode($filters, ' ') . ') ' : '',
                '@order' => $filterQuery->getOrderBy(),
                '@reverse' => ($filterQuery->getReverse()) ? 'DESC' : 'ASC',
                '@limit' => $filterQuery->getLength(),
                '@offset' => $filterQuery->getOffset()
            ]
        );

        $result = $this->_connector->query($select);
        if ($result) {
            $records = $result->fetchAll();
        }
        return ($records) ? array_map([$this, '_mapObjectIds'], $records) : [];
    }

    /**
     * Determines if a property is indexable.
     * @param string $property
     * @return boolean
     */
    private function _isPropertyIndexable($property)
    {
        $indexBlacklist = ['__id', '__revision', '__updated', '__origin'];
        return !in_array($property, $indexBlacklist);
    }

    /**
     * Determines if a value is indexable.
     * @param mixed $value
     * @return boolean
     */
    public function _isValueIndexable($value)
    {
        return (
            is_string($value)
            || is_null($value)
            || is_bool($value)
            || is_integer($value)
        );
    }

    /**
     * Method used with array_map() to return an object
     * for a given ID.
     * @param object $record
     * @return object|null
     */
    private function _mapObjectIds($record)
    {
        return $this->_getObject($record['__id']);
    }

    /**
     * Updates an object's "value" index.
     * @param object $object
     * @return void
     */
    private function _updateObjectIndexes($object)
    {
        //delete all indexed references to this object
        $update = Statement::get(
            'DELETE_OBJECT_INDEX',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($object->__id)
            ]
        );
        //parse each property of the object an attempt to index each value
        foreach (get_object_vars($object) as $property => $value) {
            if (
                $this->_isPropertyIndexable($property)
                && $this->_isValueIndexable($value)
            ) {
                $insert = Statement::get(
                    'INSERT_OBJECT_INDEX',
                    [
                        '@collection' => $this->_name,
                        '@type' => $this->_connector->quote('value'),
                        '@prop' => $this->_connector->quote($property),
                        '@val' => $this->_connector->quote($value),
                        '@id' => $this->_connector->quote($object->__id),
                        '@origin' => $this->_connector->quote($object->__origin)
                    ]
                );
                $update .= $insert;
            }
        }
        //add the object registry index
        $insert = Statement::get(
            'INSERT_OBJECT_INDEX',
            [
                '@collection' => $this->_name,
                '@type' => $this->_connector->quote('registry'),
                '@prop' => $this->_connector->quote(''),
                '@val' => $this->_connector->quote(''),
                '@id' => $this->_connector->quote($object->__id),
                '@origin' => $this->_connector->quote($object->__origin)
            ]
        );
        $update .= $insert;
        //execute all the sql to update indexes.
        $this->_connector->exec($update);
    }

    /**
     * Upserts an object into the collection. Since update and inserts are the same logic,
     * this method handles both.
     * @param object $object
     * @param string $id
     * @return void
     */
    private function _upsert($object, $id = null)
    {
        $object = $this->_writeObjectToDb($object, $id);
        $this->_updateObjectIndexes($object);
        $this->_commitObject($object->__id, $object->__revision);
        return $object;
    }

    /**
     * Part of the upsert process, this method contains logic to write an object
     * to the database. This method will also add the appropriate meta data to the object
     * and return it.
     * @param object $object
     * @param string $id
     * @return object
     */
    private function _writeObjectToDb($object, $id)
    {
        $objectId = (!is_null($id)) ? $id : $this->_generateUniqueId();
        $origin = $this->_getObjectOrigin($objectId);
        $object = json_decode(json_encode($object));
        $object->__id = $objectId;
        $object->__revision = $this->_generateRevisionNumber();
        $object->__updated = $this->_generateTimestamp();
        $object->__origin = ($origin) ? $origin : $object->__updated;

        //insert into database
        $insert = Statement::get(
            'INSERT_OBJECT',
            [
                '@collection' => $this->_name,
                '@id' => $this->_connector->quote($object->__id),
                '@revision' => $this->_connector->quote($object->__revision),
                '@committed' => $this->_connector->quote(0),
                '@updated' => $this->_connector->quote($object->__updated),
                '@origin' => $this->_connector->quote($object->__origin),
                '@obj' => $this->_connector->quote(json_encode($object))
            ]
        );
        $this->_connector->exec($insert);
        return $object;
    }

    /**
     * This method is used will return the count of objects contained with
     * the collection.
     * @return int
     */
    private function _countObjectsInCollection()
    {
        $select = Statement::get(
            'GET_COLLECTION_OBJECT_COUNT',
            [
                '@collection' => $this->_name
            ]
        );
        $count = $this->_connector->query($select)->fetch();
        if ($count) {
            return (int) $count[0];
        } else {
            return 0;
        }
    }

    /**
     * This method is used to return an object count by the filter that is passed in.
     * @param Filter $filterQuery
     * @return int
     */
    private function _countObjectsInCollectionByFilter(Filter $filterQuery)
    {
        $records = [];
        $props = [];
        $filters = [];
        foreach ($filterQuery->getComparisons() as $comparison) {
            $props[] = $comparison->prop;
            if ($comparison->expression && $comparison->comparison && $comparison->prop) {
                $expression = ($comparison->expression !== 'WHERE') ? $comparison->expression . ' ' : '';
                $prop = is_int($comparison->val) ? 'CAST(' . $comparison->prop . ' AS INT)' : $comparison->prop;
                $compare = $comparison->comparison;
                $value = (!isset($comparison->val) || is_null($comparison->val)) ? 'NULL' : $comparison->val;
                $filters[] = $expression . $prop . ' ' . $compare . ' \'' . $value . '\'';
            }
        }

        $props = array_unique($props);
        $joins = [];
        foreach ($props as $prop)
        {
            $standardFields = [
                '__id',
                '__type',
                '__collection',
                '__origin'
            ];
            if (!in_array($prop, $standardFields)) {
                $asTbl = $this->_generateAlphaToken();
                $joins[] =
                    'JOIN(' .
                        'SELECT id, val AS ' . $prop . ' ' .
                        'FROM ' . $this->_name . '__index ' .
                        'WHERE prop = \'' . $prop . '\'' .
                    ') AS ' . $asTbl . ' ' .
                    'ON A.id = ' . $asTbl . '.id';
            }
        }

        $select = Statement::get(
            'GET_OBJECTS_COUNT_BY_FILTER',
            [
                '@collection' => $this->_name,
                '@columns' => (count($props) > 0) ? ', ' . implode($props, ', ') : '',
                '@joinColumns' => (count($joins) > 0) ? implode($joins, ' ') . ' ' : '',
                '@type' => $this->_connector->quote($filterQuery->getIndexType()),
                '@filters' => ($filters) ? 'AND (' . implode($filters, ' ') . ') ' : ''
            ]
        );

        $count = $this->_connector->query($select)->fetch();
        if ($count) {
            return (int) $count[0];
        } else {
            return 0;
        }
    }
}
