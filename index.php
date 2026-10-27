<?php
use app\Configuration;

session_start();
define('BASE_URL', '/Preguntados-PW2/');
require_once("app/Configuration.php");

$configuration = new Configuration();
$router = $configuration->getRouter();

$router->dispatch(
    Request::get("controller", "login"),
    Request::get("method", "inicio")
);