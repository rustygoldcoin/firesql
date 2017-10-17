<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

$time_start = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/firesql.db');
$db = new Fire\Sql($pdo);
$myCollection = $db->collection('TestCollection');

$object = (object)[
    'firstName' => 'Joshua',
    'lastName' => 'Johnson',
    'rand' => rand(1, 200)
];

$myCollection->insert($object);
$obj = $myCollection->find($object->__id);
$obj->newStuff = true;
$myCollection->update($obj->__id, $obj);
$obj = $myCollection->find($obj->__id);
var_dump($obj);

$time_end = microtime(true);
$time = ($time_end - $time_start) * 1000;
echo '<br>Finished in <strong>' . $time . ' milliseconds</strong>';
