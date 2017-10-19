<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

$time_start = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/firesql.db');
$db = new Fire\Sql($pdo);
$collection = $db->collection('TestCollection');

// for ($i = 0; $i < 5000; $i++) {
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

$filter = new Fire\Sql\Filter();
$filter->where('rand')->gt(3);
$filter->and('rand')->lt(10);
$filter->orderBy('index');

$result = $collection->find($filter);
var_dump($result);
var_dump(count($result));

$time_end = microtime(true);
$time = ($time_end - $time_start) * 1000;
echo '<br>Finished in <strong>' . $time . ' milliseconds</strong>';
