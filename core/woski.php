<?php
ob_start();

require 'composer/vendor/autoload.php'; 
require 'helpers.php';
$_SERVER['PROJECT_ROOT'] = ($_WOSKI_ENV == strtolower("development")) ? dirname(__FILE__, 2) : $_SERVER['DOCUMENT_ROOT'];

session_start();

define('ROOT', $_SERVER['PROJECT_ROOT']);

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();
?>