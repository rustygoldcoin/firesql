<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

$time_start = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/firesql.db');
$db = new Fire\Sql($pdo);
$collection = $db->collection('TestCollection');

$filter = new Fire\Sql\Filter();
$filter->where('rand')->eq(199);
$filter->and('firstName')->eq('Josh');
$filter->orderBy('firstName');
// for ($i = 0; $i < 100; $i++) {
//     $start = microtime(true);
//     $obj = (object) [
//        'index' => $i,
//        'firstName' => 'Joshua',
//        'lastName' => 'Joshua',
//        'email' => 'josh@ua1.us',
//        'phone' => '4075628773',
//        'rand' => rand(1,200)
//     ];
//
//     $object = $collection->insert($obj);
//     var_dump($object);
//     $end = microtime(true);
//     $time = ($end - $start) * 1000;
//     echo 'doc#: ' . $i . ' docId: ' . $object->__id . ' time:' . $time . 'ms' . "\n";
// }

// $filter = (object) [
//     'rand' => 1
// ];
//
$result = $collection->find($filter, 0, 10, true);
var_dump($result);
var_dump(count($result));

$time_end = microtime(true);
$time = ($time_end - $time_start) * 1000;
echo '<br>Finished in <strong>' . $time . ' milliseconds</strong>';
