<?php

require 'core/woski.php';

$app = new Woski\Application;

// Controllers
$homeController = new App\Controllers\AppController();
$demoMW = new App\Middlewares\DemoMiddleware();

// Global Middleware
$app->use([$demoMW->handle]);

// Simple Route
$app->get('/', [$homeController->index]);

// Example Grouped Routes
$app->use("/example", [$demoMW->foo], _import("routes/example.routes.php"));

// 404 Handler
$app->error(["GET", "POST", "PUT", "PATCH"], function ($req, $res) {
    echo "404 Not Found";
});

$app->start();
