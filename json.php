<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

UMLPoll\Database::connect($DB_SERVER, $DB_USER, $DB_PASS, $DB_DATABASE);
UMLPoll\Database::charset("utf8");

$json_data = UMLPoll\Poll\Poll::get(filter_input(INPUT_GET, 'id'));

UMLPoll\Database::close();

$json = json_encode(($json_data === NULL ? ["error" => 404] : $json_data), JSON_PRETTY_PRINT);
if ($json === false) {
    $json = json_encode(array("jsonError", json_last_error_msg()));
    if ($json === false) {
        $json = '{"jsonError": "unknown"}';
    }
    http_response_code(500);
}

echo $json;
