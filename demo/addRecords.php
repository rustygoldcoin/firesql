<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// set_time_limit(0);

// require_once __DIR__ . '/../vendor/autoload.php';

// $firebug = Fire\Bug::get();
// $firebug->enable();

// $pdo = new PDO('sqlite:' . __DIR__ . '/demo.db');
// $db = new Fire\Sql($pdo);
// $collection = $db->collection('TestCollection');

// for ($i = 0; $i < 100; $i++) {
//     $start = $firebug->timer();
//     $obj = (object) [
//        'index' => $i,
//        'firstName' => 'Joshua',
//        'lastName' => 'Joshua',
//        'email' => 'josh@ua1.us',
//        'phone' => '4075628773',
//        'rand' => rand(1,10)
//     ];

//     $object = $collection->insert($obj);
//     debugger($object);
//     debugger('#' . $i . ' id:' . $object->__id . ' time:' . $firebug->timer($start) . 'ms');
// }

// echo $firebug->render();
