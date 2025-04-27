<?php
// Woski Bootstrap Code

// Start output buffering
ob_start();

// Load Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Start the session
session_start();

// Define root path constant
define('ROOT', __DIR__ . '/../');

// Load environment variables (.env)
if (file_exists(ROOT . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT);
    $dotenv->load();
}

// Set custom error log path
ini_set("log_errors", 1);
if(isset($_ENV['WOSKIPHP_ERROR_LOG_PATH'])) {
    ini_set("error_log", $_ENV['WOSKIPHP_ERROR_LOG_PATH'] . "/error_log.log");
}else {
    ini_set("error_log", ROOT . "error_log.log"); 
}

// Register Whoops error handler (pretty error pages)
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

// Load global helper functions
require_once __DIR__ . '/helpers.php';
