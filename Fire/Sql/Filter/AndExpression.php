<?php

namespace Fire\Sql\Filter;

use Fire\Sql\Filter\LogicExpression;

class AndExpression extends LogicExpression {

    public function __construct($propertyName)
    {
        $this->expression = 'AND';
        parent::__construct($propertyName);
    }

}
