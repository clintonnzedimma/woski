<?php
// Example route file

$router = new Woski\Http\Router;

$router->get("/",[function ($req, $res){
	echo "Hi Realmling";
}]);

$router->get("/hello",[function ($req, $res){
	echo "Example Hello World!";
}]);

return $router;
