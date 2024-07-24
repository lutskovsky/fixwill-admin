<?php
const TG_TOKEN = '6902073420:AAGCRZrpmj1SftYXb7p4fy5X2Zg5i8Hgrvs';

// Get the content of the incoming request
$input = file_get_contents('php://input');

//$phone = $_REQUEST['contact_phone_number'];
//$employee = $_REQUEST['employee_number'];

// Log the JSON data to a file
$logFile = 'tg_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);
