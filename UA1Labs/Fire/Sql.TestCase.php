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

namespace Test\UA1Labs\Fire;

use \UA1Labs\Fire\Test\TestCase;

/**
 * Exception class for FireSql Exceptions
 */
class SqlTestCase extends TestCase
{

    private $fireSql;

    public function beforeEach()
    {
        $this->fireSql = '';
    }

}

/**
 * Mock Classes
 */

class PDOMock extends PDO
{

}
