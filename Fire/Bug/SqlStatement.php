<?php

/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireStudio
 * @subpackage FireBug
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

namespace Fire\Bug;

class SqlStatement
{

    private $_statement;

    private $_time;

    public function __construct()
    {
        $this->_statement = '';
        $this->_time = 0;
    }

    public function setStatement($statement)
    {
        $this->_statement = $statement;
    }

    public function getStatement()
    {
        return $this->_statement;
    }

    public function setTime($time)
    {
        $this->_time = $time;
    }

    public function getTime()
    {
        return $this->_time;
    }

}
