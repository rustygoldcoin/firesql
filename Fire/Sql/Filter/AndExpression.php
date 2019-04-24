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

namespace Fire\Sql\Filter;

use Fire\Sql\Filter\LogicExpression;

/**
 * This class is a helper class used to provide a construct for AND
 * Logic Expressions to the \Fire\Sql\Filter class
 */
class AndExpression extends LogicExpression {

    /**
     * The constructor
     * @param string $propertyName
     */
    public function __construct($propertyName)
    {
        $this->expression = 'AND';
        parent::__construct($propertyName);
    }

}
