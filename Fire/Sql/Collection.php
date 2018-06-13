<?php

namespace Fire\Sql;

use \DateTime;
use \Fire\Sql\Statement;
use \Fire\Sql\Filter;
use \Fire\Sql\Connector;

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
     * Creates an instance of a new collection.
     * @param String $name The name of the collection
     * @param PDO $pdo The connection to the database
     */
    public function __construct($name, Connector $connector)
    {
        $this->_connector = $connector;
        $this->_name = $name;
    }

    public function delete($id)
    {
        $delete = Statement::get(
            'DELETE_OBJECT_INDEX',
            [
                '@id' => $this->_connector->quote($id)
            ]
        );
        $delete .= Statement::get(
            'DELETE_OBJECT',
            [
                '@id' => $this->_connector->quote($id)
            ]
        );

        $this->_connector->exec($delete);
    }

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

    public function insert($object)
    {
        return $this->_upsert($object, null);
    }

    public function update($id, $object)
    {
        $this->_upsert($object, $id);
    }

    private function _commitObject($id, $revision)
    {
        $update = Statement::get(
            'UPDATE_OBJECT_TO_COMMITTED',
            [
                '@id' => $this->_connector->quote($id),
                '@revision' => $this->_connector->quote($revision)
            ]
        );
        $this->_connector->exec($update);
    }

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
        return sha1($date->format('YmdHisu'));
    }

    private function _getObject($id, $revision = null)
    {
        if ($revision === null) {
            $select = Statement::get(
                'GET_CURRENT_OBJECT',
                [
                    '@id' => $this->_connector->quote($id)
                ]
            );
            $record = $this->_connector->query($select)->fetch();
            if ($record) {
                return json_decode($record['obj']);
            }

            return null;
        }
    }

    private function _getObjectOrigin($id)
    {
        $select = Statement::get(
            'GET_OBJECT_ORIGIN_DATE',
            [
                '@id' => $this->_connector->quote($id)
            ]
        );
        $record = $this->_connector->query($select)->fetch();
        return ($record) ? $record['updated'] : null;
    }

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
                        'SELECT id, val as ' . $prop . ' ' .
                        'FROM __index ' .
                        'WHERE prop = \'' . $prop . '\'' .
                    ') AS ' . $asTbl . ' ' .
                    'ON A.id = ' . $asTbl . '.id';
            }
        }

        $select = Statement::get(
            'GET_OBJECTS_BY_FILTER',
            [
                '@columns' => (count($props) > 0) ? ', ' . implode($props, ', ') : '',
                '@joinColumns' => (count($joins) > 0) ? implode($joins, ' ') . ' ' : '',
                '@collection' => $this->_connector->quote($this->_name),
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

    private function _isPropertyIndexable($property)
    {
        $indexBlacklist = ['__id', '__revision', '__updated', '__origin'];
        return !in_array($property, $indexBlacklist);
    }

    public function _isValueIndexable($value)
    {
        return (
            is_string($value)
            || is_null($value)
            || is_bool($value)
            || is_integer($value)
        );
    }

    private function _mapObjectIds($record)
    {
        return $this->_getObject($record['__id']);
    }

    private function _updateObjectIndexes($object)
    {
        //delete all indexed references to this object
        $update = Statement::get(
            'DELETE_OBJECT_INDEX',
            [
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
                        '@type' => $this->_connector->quote('value'),
                        '@prop' => $this->_connector->quote($property),
                        '@val' => $this->_connector->quote($value),
                        '@collection' => $this->_connector->quote($this->_name),
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
                '@type' => $this->_connector->quote('registry'),
                '@prop' => $this->_connector->quote(''),
                '@val' => $this->_connector->quote(''),
                '@collection' => $this->_connector->quote($this->_name),
                '@id' => $this->_connector->quote($object->__id),
                '@origin' => $this->_connector->quote($object->__origin)
            ]
        );
        $update .= $insert;
        //execute all the sql to update indexes.
        $this->_connector->exec($update);
    }

    private function _upsert($object, $id = null)
    {
        $object = $this->_writeObjectToDb($object, $id);
        $this->_updateObjectIndexes($object);
        $this->_commitObject($object->__id, $object->__revision);
        return $object;
    }

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
                '@collection' => $this->_connector->quote($this->_name),
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
}
