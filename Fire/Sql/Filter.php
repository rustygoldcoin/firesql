<?php
/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireSQL
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

namespace Fire\Sql;

use \Exception;
use \Fire\SqlException;
use \Fire\Sql\Statement;
use \Fire\Sql\Filter\AndExpression;
use \Fire\Sql\Filter\OrExpression;
use \Fire\Sql\Filter\WhereExpression;
use \Fire\Sql\Filter\LogicExpression;

/**
 * This class is responsbile for creating translating a set of conditions
 * into a SQL statement for finding objects within a collection. The constructor
 * of this class takes in a JSON string that will be interpreted into setup
 * the filter to return the appropriate SQL Statement.
 */
class Filter {

    const INDEX_SEARCH_TYPE_VALUE = 'value';
    const INDEX_SEARCH_TYPE_REGISTRY = 'registry';
    const COMPARISON_LOGIC_AND = 'and';
    const COMPARISON_LOGIC_OR = 'or';
    const COMPARISON_LOGIC_WHERE = 'where';
    const COMPARISON_TYPE_EQUAL = '=';
    const COMPARISON_TYPE_NOT_EQUAL = '<>';
    const COMPARISON_TYPE_GREATERTHAN = '>';
    const COMPARISON_TYPE_GREATERTHAN_EQUAL = '>=';
    const COMPARISON_TYPE_LESSTHAN = '<';
    const COMPARISON_TYPE_LESSTHAN_EQUAL = '<=';
    const FILTER_CONFIG_LENGTH = 'length';
    const FILTER_CONFIG_OFFSET = 'offset';
    const FILTER_CONFIG_ORDER_BY = 'order';
    const FILTER_CONFIG_REVERSE = 'reverse';
    const METHOD_LOGIC_TYPE_EQUAL = 'eq';
    const METHOD_LOGIC_TYPE_NOT_EQUAL = 'not';
    const METHOD_LOGIC_TYPE_GREATERTHAN = 'gt';
    const METHOD_LOGIC_TYPE_GREATERTHAN_EQUAL = 'gteq';
    const METHOD_LOGIC_TYPE_LESSTHAN = 'lt';
    const METHOD_LOGIC_TYPE_LESSTHAN_EQUAL = 'lteq';

    /**
     * The indexType indicates which index types we should be using to query
     * for objects within the database.
     * @var string
     */
    private $_indexType;

    /**
     * An array of LogicExpression objects that will be used build out
     * the SQL query.
     * @var array
     */
    private $_comparisons;

    /**
     * The field we want order the objects in the returned collection by.
     * @var string
     */
    private $_orderBy;

    private $_reverse;

    private $_offet;

    private $_length;

    public function __construct($queryString = null)
    {
        $this->_indexType = self::INDEX_SEARCH_TYPE_REGISTRY;
        $this->_comparisons = [];
        $this->_orderBy = '__origin';
        $this->_reverse = true;
        $this->_offset = 0;
        $this->_length = 10;
        if (!empty($queryString)) {
            $this->indexType(self::INDEX_SEARCH_TYPE_VALUE);
            $this->_parseQueryString($queryString);
        }
    }

    public function where($propertyName)
    {
        $this->indexType(self::INDEX_SEARCH_TYPE_VALUE);
        return $this->_addComparison(new WhereExpression($propertyName));
    }

    public function and($propertyName)
    {
        return $this->_addComparison(new AndExpression($propertyName));
    }

    public function or($propertyName)
    {
        return $this->_addComparison(new OrExpression($propertyName));
    }

    public function getComparisons()
    {
        return $this->_comparisons;
    }

    public function offset($offset)
    {
        $this->_offset = $offset;
    }

    public function getOffset()
    {
        return $this->_offset;
    }

    public function length($length)
    {
        $this->_length = $length;
    }

    public function getLength()
    {
        return $this->_length;
    }

    public function orderBy($propertyName)
    {
        $this->_orderBy = $propertyName;
        $this->_addComparison(new LogicExpression($propertyName));
    }

    public function getOrderBy()
    {
        return $this->_orderBy;
    }

    public function reverse($reverse)
    {
        $this->_reverse = $reverse;
    }

    public function getReverse()
    {
        return $this->_reverse;
    }

    public function indexType($type)
    {
        $this->_indexType = $type;
    }

    public function getIndexType()
    {
        return $this->_indexType;
    }

    public function filter()
    {
        return (object) [
            'type' => $this->_indexType,
            'filters' => $this->_comparisons,
            'order' => $this->_orderBy,
            'reverse' => $this->_reverse,
            'offset' => $this->_offset,
            'length' => $this->_length
        ];
    }

    private function _addComparison(LogicExpression $logicExpression) {
        $this->_comparisons[] = $logicExpression;
        end($this->_comparisons);
        $i = key($this->_comparisons);
        return $this->_comparisons[$i];
    }

    private function _parseQueryString($queryString)
    {
        $queryObjs = json_decode($queryString);
        if (!$queryObjs) {
            throw new SqlException(
                'There was an error parsing your filter query string. '
                . 'The filter query must be in the format of a JSON object.'
            );
        }

        //inject a single object into an array.
        if (!is_array($queryObjs)) {
            $queryObj = $queryObjs;
            $queryObjs = [];
            $queryObjs[] = $queryObj;
        }

        $firstObj = true;
        foreach ($queryObjs as $queryObj) {
            $firstProperty = true;
            foreach ($queryObj as $property => $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $this->_addFilterComparison($firstObj, $firstProperty, $property, $val);
                    }
                } else {
                    $this->_addFilterComparison($firstObj, $firstProperty, $property, $value);
                }
                $firstProperty = false;
            }
            $firstObj = false;
        }
    }

    private function _addFilterComparison($firstObj, $firstProperty, $property, $value)
    {
        if ($firstObj && $firstProperty) {
            $this->_parseComparison(self::COMPARISON_LOGIC_WHERE, $property, $value);
        } else if (!$firstObj && $firstProperty) {
            $this->_parseComparison(self::COMPARISON_LOGIC_OR, $property, $value);
        } else {
            $this->_parseComparison(self::COMPARISON_LOGIC_AND, $property, $value);
        }
    }

    private function _parseComparison($compareLogic, $property, $value)
    {
        switch ($property) {
            case self::FILTER_CONFIG_LENGTH:
                $this->length($value);
                break;
            case self::FILTER_CONFIG_OFFSET:
                $this->offset($value);
                break;
            case self::FILTER_CONFIG_ORDER_BY:
                $this->orderBy($value);
                break;
            case self::FILTER_CONFIG_REVERSE:
                $this->reverse($value);
                break;
            default:
                $compare = $this->_extractComparisonTypeAndValue($value);
                $compareType = $compare->type;
                $compareValue = $compare->value;
                $this->{$compareLogic}($property)->{$compareType}($compareValue);
        }
    }

    private function _extractComparisonTypeAndValue($val)
    {
        $validComparisons = [
            self::COMPARISON_TYPE_GREATERTHAN_EQUAL,
            self::COMPARISON_TYPE_LESSTHAN_EQUAL,
            self::COMPARISON_TYPE_NOT_EQUAL,
            self::COMPARISON_TYPE_GREATERTHAN,
            self::COMPARISON_TYPE_LESSTHAN,
            self::METHOD_LOGIC_TYPE_EQUAL
        ];
        $comparison = substr($val, 0, 2);
        switch ($comparison) {
            case self::COMPARISON_TYPE_GREATERTHAN_EQUAL:
                $type = self::METHOD_LOGIC_TYPE_GREATERTHAN_EQUAL;
                break;
            case self::COMPARISON_TYPE_LESSTHAN_EQUAL:
                $type = self::METHOD_LOGIC_TYPE_LESSTHAN_EQUAL;
                break;
            case self::COMPARISON_TYPE_NOT_EQUAL:
                $type = self::METHOD_LOGIC_TYPE_NOT_EQUAL;
                break;
        }
        if (empty($type)) {
            $comparison = substr($val, 0, 1);
            switch ($comparison) {
                case self::COMPARISON_TYPE_GREATERTHAN:
                    $type = self::METHOD_LOGIC_TYPE_GREATERTHAN;
                    break;
                case self::COMPARISON_TYPE_LESSTHAN:
                    $type = self::METHOD_LOGIC_TYPE_LESSTHAN;
                    break;
                default:
                    $type = self::METHOD_LOGIC_TYPE_EQUAL;
            }
        }

        return (object) [
            'type' => $type,
            'value' => (is_string($val) && in_array($comparison, $validComparisons))
                ? str_replace($comparison, '', $val)
                : $val
        ];
    }
}
