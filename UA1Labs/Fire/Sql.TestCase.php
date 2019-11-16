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
use \UA1Labs\Fire\Sql;
use \UA1Labs\Fire\Sql\Collection;
use \UA1Labs\Fire\Di;
use \PDO;

class SqlTestCase extends TestCase
{

    /**
     * Instance of FireDi
     *
     * @var \UA1Labs\Fire\Di
     */
    private $fireDi;

    /**
     * Instance of FireSql
     *
     * @var \UA1Labs\Fire\Sql
     */
    private $fireSql;

    public function setUp()
    {
        $this->fireDi = new Di();
    }

    public function tearDown()
    {
        unset($this->fireDi);
        unset($this->fireSql);
    }

    public function beforeEach()
    {
        $pdoMock = $this->getMockObject(PDO::class);
        $this->fireSql = $this->fireDi->getWith(Sql::class, [$pdoMock]);
    }

    public function testConstruct()
    {
        $this->should('Have constructed an instance of FireSql without thrown an exception.');
        $this->assert($this->fireSql instanceof Sql);
    }

    public function testCollection()
    {
        $this->should('Return a new collection object with the name of the collection I asked for.');
        $collection = $this->fireSql->collection('MyCollection');
        $this->assert($collection instanceof Collection);
    }

}
