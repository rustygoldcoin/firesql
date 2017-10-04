<?php

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/firesql.db');
$db = new Fire\Sql($pdo);

var_dump($db);
