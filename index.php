<?php

require "./autoload.php";

use Config\Database;
use Controllers\UserController;

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$uri = explode("/", $uri);

if ($uri[1] !== 'test') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$userId = null;
if (isset($uri[2])) {
    $userId = (int) $uri[2];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

$host = "localhost://";
$db_name = "test";
$username = "root";
$password = "";

$database = new Database($host, $db_name, $username, $password);
$db = $database->getConnection();

$controller = new UserController($db, $requestMethod, $userId);
$controller->processReq();
