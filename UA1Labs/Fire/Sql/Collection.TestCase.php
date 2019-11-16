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

namespace Test\UA1Labs\Fire\Sql;

use \UA1Labs\Fire\Test\TestCase;
use \UA1Labs\Fire\Sql\Collection;
use \UA1Labs\Fire\Sql\Connector;
use \UA1Labs\Fire\Bug;
use \PDO;

class CollectionTestCase extends TestCase
{

    /**
     * The location of the sqlite db file for this
     * test to run.
     *
     * @var string
     */
    private $demoDb;

    /**
     * Instance of \UA1Labs\Fire\Sql\Collection
     *
     * @var \UA1Labs\Fire\Sql\Collection
     */
    private $collection;

    public function setUp()
    {
        Bug::get()->enable();
        $this->demoDb = __DIR__ . '/demo.db';
    }

    public function tearDown()
    {
        unset($this->collection);
        unlink($this->demoDb);
    }

    public function beforeEach()
    {
        $pdo = new PDO('sqlite:' . $this->demoDb);
        $connector = new Connector($pdo);
        $this->collection = new Collection('MyCollection', $connector);
    }

    public function testConstruct()
    {
        $this->should('Have returned a collection object without thrown an exception.');
        $this->assert($this->collection instanceof Collection);
    }

    public function testInsert()
    {
        $this->should('Successfully insert an object into the database.');
        $obj = (object) [
            'companyName' => 'UA1 Labs'
        ];
        $final = $this->collection->insert($obj);
        $this->assert($final->companyName === 'UA1 Labs');

        $this->should('Contain an __id after the object is inserted.');
        $this->assert(isset($final->__id));

        $this->should('Contain a __revision after the object is inserted.');
        $this->assert(isset($final->__revision));

        $this->should('Contain a __updated and __origin date and they should be the same.');
        $this->assert(
            isset($final->__updated)
            && isset($final->__origin)
            && $final->__updated === $final->__origin
        );
    }

    public function testUpdate()
    {
        $this->should('Update the object in the datbase with updated values.');
        $obj = (object) [
            'companyName' => 'UA1 Labs'
        ];
        $origin = $this->collection->insert($obj);
        $origin->isAwesome = true;
        $updated = $this->collection->update($origin->__id, $origin);
        $this->assert($updated->isAwesome === true);

        $this->should('Have updated the revision after the update.');
        $this->assert($origin->__updated !== $updated->__updated);

        $this->should('Have a different update date than origin date.');
        $this->assert($updated->__origin !== $updated->__updated);

        $this->should('Have a different revision number than the origin');
        $this->assert($origin->__revision !== $updated->__revision);
    }

    public function testDelete()
    {
        $this->should('Delete the object from the database.');
        $obj = (object) [
            'companyName' => 'UA1 Labs'
        ];
        $origin = $this->collection->insert($obj);
        $this->collection->delete($origin->__id);
        $find = $this->collection->find($origin->__id);
        $this->assert($find === null);
    }

    public function testFind()
    {
        for ($i = 1; $i <= 10; $i++) {
            $obj = (object) [
                'i' => $i,
                'rand' => 10
            ];
            $lastObj = $this->collection->insert($obj);
        }
        $this->should('Return the correct object if we pass in the ID of the object.');
        $obj = $this->collection->find($lastObj->__id);
        $this->assert($obj->i === $lastObj->i);

        $this->should('Return the correct object if we query by basic JSON string.');
        $response = $this->collection->find('{"i": 2}');
        $this->assert($response[0]->i === 2);



    }

}
