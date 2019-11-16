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

namespace UA1Labs\Fire\Sql\Filter;

use \UA1Labs\Fire\Sql\Filter\LogicExpression;

/**
 * This class is a helper class used to provide a construct for WHERE
 * Logic Expressions to the \UA1Labs\Fire\Sql\Filter class
 */
class WhereExpression extends LogicExpression 
{

    const WHERE_EXPRESSION = 'WHERE';

    /**
     * The class constructor.
     * 
     * @param string $propertyName
     */
    public function __construct($propertyName)
    {
        $this->expression = self::WHERE_EXPRESSION;
        parent::__construct($propertyName);
    }
}
