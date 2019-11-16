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

namespace UA1Labs\Fire\Sql\Panel;

class SqlStatement
{

    /**
     * The SQL statement.
     *
     * @var string
     */
    private $statement;

    /**
     * The SQL expression execution time in milliseconds
     *
     * @var float
     */
    private $time;

    /**
     * The PHP stack trace
     *
     * @var array
     */
    private $trace;

    /**
     * The class constructor.
     */
    public function __construct()
    {
        $this->statement = '';
        $this->time = 0;
        $this->trace = [];
    }

    /**
     * Sets the SQL statement.
     *
     * @param string $statement
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
    }

    /**
     * Returns the SQL statement.
     *
     * @return string
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Sets the time.
     *
     * @param float $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Returns the time.
     *
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns the stacktrace.
     *
     * @return array
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Sets the stacktrace
     *
     * @param array $trace
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
    }

}
