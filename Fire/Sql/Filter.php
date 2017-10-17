<?php

namespace Fire\Sql;

use Fire\Sql\Statement;
use Fire\Sql\Filter\AndExpression;
use Fire\Sql\Filter\OrExpression;
use Fire\Sql\Filter\LogicExpression;

class Filter {

    private $_filters;

    private $_order;

    private $_reverse;

    private $_offet;

    private $_length;

    public function __construct()
    {
        $this->_filters = [];
        $this->_order = 'origin';
        $this->_reverse = false;
        $this->_offset = 0;
        $this->_length = 10;
    }

    public function and($propertyName)
    {
        return $this->_addLogic(new AndExpression($propertyName));
    }

    public function getFilterModel()
    {
        return (object) [
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
        $this->_order = $propertyname;
    }

    public function reverse($reverse)
    {
        $this->_reverse = $reverse;
    }

    private function _addLogic(LogicExpression $logicExpression) {
        $this->_filters[] = $logicExpression;
        end($this->_filters);
        $i = key($this->_filters);
        return $this->_filters[$i];
    }
}
