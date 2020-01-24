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
use \UA1Labs\Fire\SqlException;
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
        
        $this->should('Have thrown an Exception when you try to configure the collection with an improper versionTracking setting.');
        try {
            $pdo = new PDO('sqlite:' . $this->demoDb);
            $connector = new Connector($pdo);
            $collection = new Collection('TestCollection1', $connector, ['versionTracking' => 1]);
            $this->assert(false);
        } catch (SqlException $e) {
            $this->assert(true);
        }

        $this->should('Have thrown an Exception when you try to configure the collection with an improper model setting.');
        try {
            $pdo = new PDO('sqlite:' . $this->demoDb);
            $connector = new Connector($pdo);
            $collection = new Collection('TestCollection1', $connector, ['model' => 1]);
            $this->assert(false);
        } catch (SqlException $e) {
            $this->assert(true);
        }

        $this->should('Have thrown an Exception when you try to configure the collection with a model that defines a class that does not exist.');
        try {
            $pdo = new PDO('sqlite:' . $this->demoDb);
            $connector = new Connector($pdo);
            $collection = new Collection('TestCollection1', $connector, ['model' => 'NoClass']);
            $this->assert(false);
        } catch (SqlException $e) {
            $this->assert(true);
        }

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

        $this->should('Return an object of the type that is configured for the collection, once an object is inserted into the collection.');
        $pdo = new PDO('sqlite:' . $this->demoDb);
        $connector = new Connector($pdo);
        $collection = new Collection('MyCollection', $connector, ['model' => TestModel::class]);
        $response = $collection->insert(new TestModel());
        $this->assert($response instanceof TestModel);

        $this->should('Throw an exception when you try to insert an object that does not match the collection model type.');
        $pdo = new PDO('sqlite:' . $this->demoDb);
        $connector = new Connector($pdo);
        $collection = new Collection('MyCollection', $connector, ['model' => TestModel::class]);
        try {
            $response = $collection->insert((object) []);
            $this->assert(false);
        } catch (SqlException $e) {
            $this->assert(true);
        }

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

        $this->should('Throw an exception when you try to update an object that does not match the collection model type.');
        $pdo = new PDO('sqlite:' . $this->demoDb);
        $connector = new Connector($pdo);
        $collection = new Collection('MyCollection', $connector, ['model' => TestModel::class]);
        $object = $collection->insert(new TestModel());
        try {
            $response = $collection->update($object->__id, (object) []);
            $this->assert(false);
        } catch (SqlException $e) {
            $this->assert(true);
        }
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
        $odd = true;
        for ($i = 1; $i <= 6; $i++) {
            $obj = (object) [
                'i' => $i,
                'odd' => $odd,
                'collection' => ($i <= 3) ? 1 : 2
            ];
            $odd = !$odd;
            $lastObj = $this->collection->insert($obj);
        }

        // tests when just a string id is passed in $collectino->find("3daj32j235242j52342jgadj32");
        $this->should('Return the correct object if we pass in the ID of the object.');
        $obj = $this->collection->find($lastObj->__id);
        $this->assert($obj->i === $lastObj->i);

        // tests that a single item is returned when we pass an ID into ::find()
        $this->should('Return a single object when the pass in an ID for a single object.');
        $this->assert(is_object($obj));

        // tests when a simple JSON string is passed in $collection->find('{"i": 2}')
        $this->should('Return the correct object if we query by basic JSON string.');
        $response = $this->collection->find('{"i": 2}');
        $this->assert($response[0]->i === 2);

        // tests that when a we are using a JSON search query, the response is an array
        $this->should('Return an array when we use a JSON search query to find objects in the collection.');
        $this->assert(is_array($response));

        // tests when an AND condition exists in the JSON string
        $this->should('Return the correct objects if we query by JSON string with multiple conditions.');
        $response = $this->collection->find('{"i": 2, "odd": false}');
        $this->assert(isset($response[0]) && $response[0]->i === 2);

        // tests when an AND condition exists and we would expect to get no results
        $this->should('Returns 0 results because the and condition will include a combination that does not exist');
        $response = $this->collection->find('{"i": 2, "odd": true}');
        $this->assert(is_array($response) && empty($response));

        // tests to ensure we are getting correct collections back with AND conditions
        $this->should('Return objects in collection 1 where odd is true.');
        $response = $this->collection->find('{"odd": true, "collection": 1}');
        $this->assert(count($response) === 2);
        $is = [1, 3];
        foreach ($response as $obj) {
            $this->should('Return a response that contains an index we expect.');
            $this->assert(in_array($obj->i, $is));
        }

        // tests to ensure that we are getting correct results for an OR condition
        $this->should('Return a correct response of an OR condition.');
        $response = $this->collection->find('[{"odd": true},{"odd": false}]');
        $this->assert(count($response) === 6);

        // tests to ensure that we are getting correct results when OR statement only includes 1 match
        $this->should('Return a correct response for a query with an OR statement that is only half true.');
        $response = $this->collection->find('[{"odd": true},{"i": 10}]');
        $this->assert(count($response) === 3);
        
        $this->should('Return a collection of objects that have a type of the model we set in the options.');
        $pdo = new PDO('sqlite:' . $this->demoDb);
        $connector = new Connector($pdo);
        $collection = new Collection('MyCollection', $connector, ['model' => TestModel::class]);
        $response = $collection->find('{"i": 2, "odd": false}');
        $this->assert($response[0] instanceof TestModel);
    }

    public function testCount()
    {
        $this->should('Return the correct count for OR statements.');
        $count = $this->collection->count('[{"odd": true},{"i": 10}]');
        $this->assert($count === 3);

        $this->should('Return the correct count for all objects in collection.');
        $count = $this->collection->count('{}');
        $this->assert($count === 8);
    }

}

class TestModel
{}
