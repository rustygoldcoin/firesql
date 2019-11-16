# FireSQL

Set your SQL database on fire by making it into a NoSQL database!

**Table of Contents**

- [Getting Started](#getting-started)
- [Finding Objects](#finding-objects)
- [Advance Filtering](#advance-filtering)
- [API](#api)
- [FireBug Debug Panel](#fireBug-debug-panel)

## Why Build NoSQL Funcitonality On a Relational Database?

The problem with most relationtional databases is the fact the their schemas get in the way of you being able to be swift in making changes to data structures. One of the major problems with maintaining relationtional databases is the fact that when you update schemas, you also need to issue, and manage, update scripts that could break systems if they are installed in the incorrect order or fail to run all together. It can become costly to maintain these systems. Projects like Wordpress have been able to circumvent these issues by mostly sticking to the same database data structures they have maintained since the beginning of the project. Over the years, Wordpress has had minimal changes to the data schema they were lauched with.

## Getting Started

### Installing FireSQL Into Your Project

FireSQL is registered with [Composer](https://packagist.org/packages/ua1-labs/firesql). You may install it with composer using the command:

    composer require ua1-labs/firesql

### Connecting To A Database

To connect FireSQL to your database, you have to provide a standard PHP PDO object at the instanciation of the FireSQL Class. **NOTE: Currently, the only two database types FireSQL supports is MySQL and SQLite.**

    $pdo = new \PDO('sqlite:' . __DIR__ . '/demo.db');
    $db = new \UA1Labs\Fire\Sql($pdo);

### Getting A Collection

At some point, you are going to want to interact with data in the database itself. Whether that is inserting, updating, deleting, or reading that data. In the concept of NoSQL or Non-Relationtional databases, we bundle objects together in what we call "collections." A collection is a place to store similar objects. Not that the object schema matters, when you store objects with a similar nature of schema, it will be a lot easier to query them.

When obtaining a collection, you do not need to register it. Simply by asking for it by name, FireSQL will return an object that will have access to the collection you are asking for.

    $collection = $db->collection('CollectionName');

**Collection Options**

When asking for a collection, you have the ability to pass in options that will allow the collection to enable certain features for that collection.

    $options = [
        'versionTracking' => true
    ];
    $collection = $db->collection('CollectionName', $options);

*Available Options*

| Option | Description | Value Type | Default Value |
| - | - | - | - |
| versionTracking | Turns on version tracking for objects within the collection. So whenever an object is updated or deleted, there will always be a trail of all past values for the objects within the collection. | boolean | false |

### Inserting Your First Object

When dealing with objects in FireSQL, you can insert any object in a collection. You don't have to worry about the schema of the object. When inserting an object into the collection, FireSQL will attempt to serialize the object as a JSON string and store that as the record. Each record is stored with other information as well and also an index that will allow us to later retrieve the object.

Inserting an object:

    $vehicle = new Vehicle();
    $vehicle->setMake('Honda');
    $vehicle->setModel('CRV');
    $vehicle->setType('SUV');

    $insertedObject = $carCollection->insert($vehicle);

Please take note that `$insertedObject` is no longer an instance of `Vehicle`. Instead it is an instance of `stdClass` as FireSQL does not maintain the instance object. Rather, just the public data within the object.

Also, when an object is stored in the database, as a part of storing the object, there are 4 fields added to every object that will provide you with information about that object and how it is stored in the database.

    $insertedObject->__id; // the unique ID the object was stored as
    $insertedObject->__revision // the revision number of the object
    $insertedObject->__updated // the datetime the object was updated
    $insertedObject->__origin // the datetime the object was first stored in the database

### Updating an Object

Updating an object is in essents the same process as inserting one. In fact, behind the scenes it uses the very same logic for both updating and inserting. The only difference, is that when you update, you provide an ID for which object you will be updating.

    $updatedObject = $carCollection->update($id, $vehicle);

### Deleting an Object

Deleting objects is very easy. They are deleted by ID.

    $collection->delete($id);

### Getting Object Count

## Finding Objects

The most complex part of FireSQL is actually getting objects back out of the database once you have inserted them. For the most part, most of your searches will be easy to implement. So with that in mind, we will first talk about the easy way to get records out of the database. We will then later on talk about how to use [Advance Filtering](#advance-filtering) to execute more complex searches.

**Object Indexing**

To achieve the ability to search through objects in a collection, every time an object in inserted or updated within the collection, FireSQL will index objects by first level public members. At this point, indexes provide the ability to search by simple comparisons. We do not have any intention to implement deep object indexing within FireSQL. A value is only indexable if it lives on the root level of the object and it is either a string, integer, null, or boolean. No other data types are currently supported.

**Get Object By ID**

This is the simplest way to get objects out of the database. By searching for them by the ID that you already know.

    $object = $collection->find($id);

The above method call will return a single object from the database by the given ID. If the collection does not contain an object with the given ID, then `null` will be returned.

**Get Object By Simple Filter**

Simple filters were a concept created to allow users to pass in JSON strings that represent searches. These JSON string directly represent the objects we are trying to match.

*Direct Comparison*

If you would like to find all objects in a collection that have exact values the example below will give you an idea on how to do that. In the example, you will notice that we are asking for all objects that is the equivalent of `$obj->name = 'josh'`

    $search = '{"name":"josh"}';
    $objects = $collection->find($search);

*Other Comparison Types*

FireSQL simple filters also supports using other comparison types other than just direct comparison. In the example below, we want to retrieve all objects that contain a `$num` greater than 5.

    $search = '{"num":">5"}';
    $objects = $collection->find($search);

Supported Comparisons:

| Comparison | Example |
| - | - |
| Equal | {"name":"josh"} or {"name":"=josh"} |
| Not Equal | {"name":"<>josh"} |
| Greater Than | {"num":">5"} |
| Greater Than or Equal To | {"num":">=5"} |
| Less Than | {"num":"<5"} |
| Less Than or Equal To | {"num":"<=5"} |

*AND Logic*

Simple filters also supports AND logic where you can group together multiple comparisons. The example below will return all objects within the collection that have `$obj->name = 'josh'` and `$obj->num` is not equal to `5`.

    $search = '{"name":"josh", "num":"<>5"}';
    $objects = $collection->find($search);

*OR Logic*

OR Logic is just as simple as AND logic. With OR logic, you would just simple group comparisons within different objects. The example below will return a collection of objects who's name is set to "josh" or "steve".

    $search = '[{"name":"josh"},{"name":"steve"}]';
    $objects = $collection->find($search);

*Magic Filter Methods*

Built in the concept of simple filters is the ability to further manipulate the collection of objects before it gets returned to you. Below you will find each method and a description of how it will manipulate the collection set.

| Method | Description | Example |
| - | - | - |
| length | This method sets the number of objects you want to be returned from the entire collection | {"length": "100"} |
| offset | This method sets how we should offset the dataset by | {"offset": "10"} |
| order | This method dictates which field the dataset will be ordered by | {"order": "field"} |
| reverse | This method will determine if the order should be reversed from its natural accending order | {"reverse": true} |
