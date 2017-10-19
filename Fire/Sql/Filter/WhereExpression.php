<?php

namespace Fire\Sql\Filter;

use Fire\Sql\Filter\LogicExpression;

class WhereExpression extends LogicExpression {

    public function __construct($propertyName)
    {
        $this->expression = 'WHERE';
        parent::__construct($propertyName);
    }
}
