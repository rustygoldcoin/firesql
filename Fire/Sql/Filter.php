<?php

namespace Fire\Sql;

use \Exception;
use \Fire\SqlException;
use \Fire\Sql\Statement;
use \Fire\Sql\Filter\AndExpression;
use \Fire\Sql\Filter\OrExpression;
use \Fire\Sql\Filter\WhereExpression;
use \Fire\Sql\Filter\LogicExpression;

class Filter {

    const INDEX_SEARCH_TYPE_VALUE = 'value';
    const INDEX_SEARCH_TYPE_REGISTRY = 'registry';
    const COMPARISON_LOGIC_AND = 'and';
    const COMPARISON_LOGIC_OR = 'or';
    const COMPARISON_TYPE_EQUAL = '=';
    const COMPARISON_TYPE_NOT_EQUAL = '<>';
    const COMPARISON_TYPE_GREATERTHAN = '>';
    const COMPARISON_TYPE_GREATERTHAN_EQUAL = '>=';
    const COMPARISON_TYPE_LESSTHAN = '<';
    const COMPARISON_TYPE_LESSTHAN_EQUAL = '<=';

    private $_type;

    private $_filters;

    private $_order;

    private $_reverse;

    private $_offet;

    private $_length;

    public function __construct($queryString = null)
    {
        $this->_type = self::INDEX_SEARCH_TYPE_REGISTRY;
        $this->_filters = [];
        $this->_order = '__origin';
        $this->_reverse = true;
        $this->_offset = 0;
        $this->_length = -1;
        if (!empty($queryString)) {
            $this->type(self::INDEX_SEARCH_TYPE_VALUE);
            $this->_parseQueryString($queryString);
        }
    }

    public function and($propertyName)
    {
        return $this->_addLogic(new AndExpression($propertyName));
    }

    public function filter()
    {
        return (object) [
            'type' => $this->_type,
            'filters' => $this->_filters,
            'order' => $this->_order,
            'reverse' => $this->_reverse,
            'offset' => $this->_offset,
            'length' => $this->_length
        ];
    }

    public function length($length)
    {
        $this->_length = $length;
    }

    public function or($propertyName)
    {
        return $this->_addLogic(new OrExpression($propertyName));
    }

    public function offset($offset)
    {
        $this->_offset = $offset;
    }

    public function orderby($propertyName)
    {
        $this->_order = $propertyName;
        $this->_addLogic(new LogicExpression($propertyName));
    }

    public function reverse($reverse)
    {
        $this->_reverse = $reverse;
    }

    public function type($type)
    {
        $this->_type = $type;
    }

    public function where($propertyName)
    {
        $this->type(self::INDEX_SEARCH_TYPE_VALUE);
        return $this->_addLogic(new WhereExpression($propertyName));
    }

    private function _addLogic(LogicExpression $logicExpression) {
        $this->_filters[] = $logicExpression;
        end($this->_filters);
        $i = key($this->_filters);
        return $this->_filters[$i];
    }

    private function _parseQueryString($str)
    {
        $query = json_decode($str);
        if (!$query) {
            throw new SqlException('There was an error parsing your filter query string.');
        }

        foreach ($query as $prop => $val) {
            if (is_array($val)) {
                foreach ($val as $comparison) {
                    $compare = $this->_extractComparisonTypeAndValue($comparison);
                    $method = $compare->type;
                    $val = $compare->value;
                    $this->_configureComparison(self::COMPARISON_LOGIC_AND, $method, $prop, $val);
                }
            }
        }
    }

    private function _extractComparisonTypeAndValue($val)
    {
        $comparison = substr($val, 0, 2);
        switch ($comparison) {
            case '>=':
                $compare = 'gteq';
                break;
            case '<=':
                $compare = 'lteq';
                break;
            case '<>':
                $compare = 'not';
                break;
        }
        if (empty($compare)) {
            $comparison = substr($val, 0, 1);
            switch ($comparison) {
                case '>':
                    $compare = 'gt';
                    break;
                case '<':
                    $compare = 'lt';
                    break;
                default:
                    $compare = 'eq';
            }
        }
        return (object) [
            'type' => $compare,
            'value' => (is_string($val)) ? str_replace($comparison, '', $val) : $val
        ];
    }

    private function _configureComparison($logic, $comparison, $prop, $value)
    {
        $this->{$logic}($prop)->{$comparison}($val);
    }
}
