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

use \UA1Labs\Fire\Bug\Panel;
use \UA1Labs\Fire\Sql\Panel\SqlStatement;

/**
 * The class that represents the debug panel when you're using
 * FireBug within your project.
 */
class FireSqlPanel extends Panel
{

    const ID = 'firesql';

    /**
     * An array of sql statement objects.
     *
     * @var array<\UA1Labs\Fire\Sql\Panel\SqlStatement>;
     */
    private $statements;

    /**
     * The class constructor.
     */
    public function __construct()
    {
        $this->statements = [];
        parent::__construct(self::ID, 'FireSql Queries {{count}}', __DIR__ . '/firesql.php');
        $this->setDescription(
            'This panel shows all the Sql Statements that have been executed ' .
            'by FireSql. The statements below include a stack trace and the amount ' .
            'of time each statement took to execute.'
        );
    }

    /**
     * Adds a sql statement object to the statements array.
     *
     * @param \UA1Labs\Fire\Sql\Panel\SqlStatement $statement The sql statement
     */
    public function addSqlStatement(SqlStatement $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * Returns all of the sql statements.
     *
     * @return array<\UA1Labs\Fire\Sql\Panel\SqlStatement>
     */
    public function getSqlStatements()
    {
        return $this->statements;
    }

    /**
     * Renders this panel.
     */
    public function render()
    {
        $statementCount = count($this->statements);
        $this->setName(str_replace('{{count}}', '{' . $statementCount . '}', $this->name));
        parent::render();
    }

}
