<?php

namespace Fire\Sql;

use Fire\Sql\Statement;
use Fire\Sql\Filter\AndExpression;
use Fire\Sql\Filter\OrExpression;
use Fire\Sql\Filter\LogicExpression;

class Filter {

    private $_type;

    private $_filters;

    private $_order;

    private $_reverse;

    private $_offet;

    private $_length;

    public function __construct()
    {
        $this->_type = 'value';
        $this->_filters = [];
        $this->_order = 'origin';
        $this->_reverse = true;
        $this->_offset = 0;
        $this->_length = -1;
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

    public function orderBy($propertyName)
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
        return $this->_addLogic(new AndExpression($propertyName));
    }

    private function _addLogic(LogicExpression $logicExpression) {
        $this->_filters[] = $logicExpression;
        end($this->_filters);
        $i = key($this->_filters);
        return $this->_filters[$i];
    }
}
