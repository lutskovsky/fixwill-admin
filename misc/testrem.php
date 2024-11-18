<?php
require "../vendor/autoload.php";

use App\Integrations\RemonlineApi;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();


$remonline = new RemonlineApi();

$client = [
    'id' => 32930705,
    'name' => 'new333 testapi',
    'email' => 'new@testapi.ru',
    'address' => 'new testapi address',
    'notes' => 'new testapi notes',
    'custom_fields' => ["3532130" => "444"], //'{"3532130":"444"}',
    'phone' => [
        '79990000004',
        '79990000003',
    ]
];

$resp = $remonline->updateClient($client);
print_r($resp);
die();
