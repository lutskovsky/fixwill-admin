<?php
require "../vendor/autoload.php";

use Fixwill\RemonlineApi;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();


$remonline = new RemonlineApi();

$client = [
    'id' => 25257749,
    'name' => 'new testapi',
    'email' => 'new@testapi.ru',
//    'address' => 'new testapi address',
//    'notes' => 'new testapi notes',
//    'custom_fields' => ,
//    'phone' => [
//        '79990000001',
//        '79990000002',
//    ]
];

$resp = $remonline->updateClient($client);
print_r($resp);
die();
