<?php

$_WOSKI_ENV = "development";
require 'woski_autoload.php';
require 'core/woski.php';


$app = new Woski\Application;

$testRoute = _import("testRoute.php");

$homeController = new App\Controllers\AppController();

$app->get('/', $homeController->index);

$app->use("/test",$testRoute);

$app->start();
