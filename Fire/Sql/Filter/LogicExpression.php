<?php

namespace Fire\Sql\Filter;

class LogicExpression {

    public $expression;

    public $prop;

    public $val;

    public $comparison;

    public function __construct($propertyName)
    {
        $this->prop = $propertyName;
    }

    public function eq($value)
    {
        $this->comparison = '=';
        $this->val = $value;
    }

    public function not($value)
    {
        $this->comparison = '<>';
        $this->val = $value;
    }

    public function gt($value)
    {
        $this->comparison = '>';
        $this->val = $value;
    }

    public function lt($value)
    {
        $this->comparison = '<';
        $this->val = $value;
    }

    public function gteq($value)
    {
        $this->comparison = '>=';
        $this->val = $value;
    }

    public function lteq($value)
    {
        $this->comparison = '<=';
        $this->val = $value;
    }

}
