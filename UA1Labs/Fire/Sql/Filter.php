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

namespace UA1Labs\Fire\Sql;

use \Exception;
use \UA1Labs\Fire\SqlException;
use \UA1Labs\Fire\Sql\Statement;
use \UA1Labs\Fire\Sql\Filter\AndExpression;
use \UA1Labs\Fire\Sql\Filter\OrExpression;
use \UA1Labs\Fire\Sql\Filter\WhereExpression;
use \UA1Labs\Fire\Sql\Filter\LogicExpression;

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
    const FILTER_CONFIGlength = 'length';
    const FILTER_CONFIGoffset = 'offset';
    const FILTER_CONFIG_ORDER_BY = 'order';
    const FILTER_CONFIGreverse = 'reverse';
    const METHOD_LOGIC_TYPE_EQUAL = 'eq';
    const METHOD_LOGIC_TYPE_NOT_EQUAL = 'not';
    const METHOD_LOGIC_TYPE_GREATERTHAN = 'gt';
    const METHOD_LOGIC_TYPE_GREATERTHAN_EQUAL = 'gteq';
    const METHOD_LOGIC_TYPE_LESSTHAN = 'lt';
    const METHOD_LOGIC_TYPE_LESSTHAN_EQUAL = 'lteq';

    /**
     * The indexType indicates which index types we should be using to query
     * for objects within the database.
     *
     * @var string
     */
    private $indexType;

    /**
     * An array of LogicExpression objects that will be used build out
     * the SQL query.
     *
     * @var array<\UA1Labs\Fire\Sql\LogicExpression>
     */
    private $comparisons;

    /**
     * The field we want order the objects in the returned collection by.
     *
     * @var string
     */
    private $orderBy;

    /**
     * Determines if we will return the results in reverse order.
     *
     * @var boolean
     */
    private $reverse;

    /**
     * Determines how far we should offset the search by number of records.
     *
     * @var integer
     */
    private $offset;

    /**
     * The length of records we should return.
     *
     * @var integer
     */
    private $length;

    /**
     * The class constructor.
     *
     * @param string $queryString A JSON string that represents the filtering we want to
     * acheive.
     */
    public function __construct($queryString = null)
    {
        $this->indexType = self::INDEX_SEARCH_TYPE_REGISTRY;
        $this->comparisons = [];
        $this->orderBy = '__origin';
        $this->reverse = true;
        $this->offset = 0;
        $this->length = 10;
        if (!empty($queryString)) {
            $this->indexType(self::INDEX_SEARCH_TYPE_VALUE);
            $this->parseQueryString($queryString);
        }
    }

    /**
     * Adds a WHERE logic operator to the search statement.
     *
     * @param string $propertyName The name of the property you would like to search on
     * @return \UA1Labs\Fire\Sql\Filter\LogicExpression
     */
    public function where($propertyName)
    {
        $this->indexType(self::INDEX_SEARCH_TYPE_VALUE);
        return $this->addComparison(new WhereExpression($propertyName));
    }

    /**
     * Adds an AND logic operator to the search statement.
     *
     * @param string $propertyName The name of the property you would like to search on
     * @return \UA1Labs\Fire\Sql\Filter\LogicExpression
     */
    public function and($propertyName)
    {
        return $this->addComparison(new AndExpression($propertyName));
    }

    /**
     * Adds an OR logic operator to the search statement.
     *
     * @param string $propertyName The name of the property you would like to search on
     * @return \UA1Labs\Fire\Sql\Filter\LogicExpression
     */
    public function or($propertyName)
    {
        return $this->addComparison(new OrExpression($propertyName));
    }

    /**
     * Returns the comparisons registered.
     *
     * @return array<\UA1Labs\Fire\Sql\LogicExpression>
     */
    public function getComparisons()
    {
        return $this->comparisons;
    }

    /**
     * Sets the offset.
     *
     * @param integer $offset
     */
    public function offset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Gets the offset.
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Sets the length.
     *
     * @param integer $length
     */
    public function length($length)
    {
        $this->length = $length;
    }

    /**
     * Gets the length.
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Sets the orderby field
     *
     * @param string $propertyName
     */
    public function orderBy($propertyName)
    {
        $this->orderBy = $propertyName;
        $this->addComparison(new LogicExpression($propertyName));
    }

    /**
     * Gets the orderby field.
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Sets the reverse.
     *
     * @param boolean $reverse
     */
    public function reverse($reverse)
    {
        $this->reverse = $reverse;
    }

    /**
     * Gets the reverse.
     *
     * @return boolean
     */
    public function getReverse()
    {
        return $this->reverse;
    }

    /**
     * Sets the index type.
     *
     * @param string $type
     */
    public function indexType($type)
    {
        $this->indexType = $type;
    }

    /**
     * Gets the index type.
     *
     * @return string
     */
    public function getIndexType()
    {
        return $this->indexType;
    }

    /**
     * Returns the filter model.
     *
     * @return object
     */
    public function filter()
    {
        return (object) [
            'type' => $this->indexType,
            'filters' => $this->comparisons,
            'order' => $this->orderBy,
            'reverse' => $this->reverse,
            'offset' => $this->offset,
            'length' => $this->length
        ];
    }

    /**
     * Used to add logic expressions to the comparisons array.
     *
     * @param LogicExpression $logicExpression
     * @return \Fire\Sql\Filter\LogicExpression
     */
    private function addComparison(LogicExpression $logicExpression) {
        $this->comparisons[] = $logicExpression;
        end($this->comparisons);
        $i = key($this->comparisons);
        return $this->comparisons[$i];
    }

    /**
     * Logic used to parse the JSON $queryString from the constructor and set the
     * appropriate comparisons, length, reverse, etc.
     *
     * @param string $queryString
     * @return void
     */
    private function parseQueryString($queryString)
    {
        $queryObjs = json_decode($queryString);
        if (!$queryObjs) {
            throw new SqlException(
                'There was an error parsing your filter query string. '
                . 'The filter query must be in the format of a JSON object.'
            );
        }

        // inject a single object into an array.
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
                        $this->addFilterComparison($firstObj, $firstProperty, $property, $val);
                    }
                } else {
                    $this->addFilterComparison($firstObj, $firstProperty, $property, $value);
                }
                $firstProperty = false;
            }
            $firstObj = false;
        }
    }

    /**
     * Adds a comparison to the filter from the queryString.
     *
     * @param boolean $firstObj
     * @param boolean $firstProperty
     * @param string $property
     * @param mixed $value
     */
    private function addFilterComparison($firstObj, $firstProperty, $property, $value)
    {
        if ($firstObj && $firstProperty) {
            $this->parseComparison(self::COMPARISON_LOGIC_WHERE, $property, $value);
        } else if (!$firstObj && $firstProperty) {
            $this->parseComparison(self::COMPARISON_LOGIC_OR, $property, $value);
        } else {
            $this->parseComparison(self::COMPARISON_LOGIC_AND, $property, $value);
        }
    }

    /**
     * Parses comparisons and adds logic to the filter.
     *
     * @param string $compareLogic
     * @param string $property
     * @param mixed $value
     */
    private function parseComparison($compareLogic, $property, $value)
    {
        switch ($property) {
            case self::FILTER_CONFIGlength:
                $this->length($value);
                break;
            case self::FILTER_CONFIGoffset:
                $this->offset($value);
                break;
            case self::FILTER_CONFIG_ORDER_BY:
                $this->orderBy($value);
                break;
            case self::FILTER_CONFIGreverse:
                $this->reverse($value);
                break;
            default:
                $compare = $this->extractComparisonTypeAndValue($value);
                $compareType = $compare->type;
                $compareValue = $compare->value;
                $this->{$compareLogic}($property)->{$compareType}($compareValue);
        }
    }

    /**
     * Returns comparison value and type.
     *
     * @param mixed $val
     * @return object
     */
    private function extractComparisonTypeAndValue($val)
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
