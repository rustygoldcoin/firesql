<?php

namespace Fire\Sql\Filter;

use Fire\Sql\Filter\LogicExpression;

class OrExpression extends LogicExpression {

    public function __construct($propertyName)
    {
        $this->expression = 'OR';
        parent::__construct($propertyName);
    }
}
