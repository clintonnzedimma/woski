<?php

$router = new Woski\Http\Router;

$router->get("/",function ($req, $res){
	echo "Hi Realmling";
});




return $router;
