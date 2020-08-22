<?php
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package Application
*/ 

namespace Woski; 
use Woski\Http\Router;

class Application extends Router
{
	
	function __construct(){
		parent::__construct();
	}

  public function use ($arg1, $arg2 = null) {

      $middleware = null;
      $routePath = null;
      $middlewareRouter = null;

      if (is_callable($arg1) && $arg2 == null) {
        $middleware = $arg1;
        array_push($this->middlewares, $middleware);
      }else if (is_string($arg1) && $arg2 instanceof Router) {
        $routePath = $arg1;
        $middlewareRouter = $arg2;
        $middlewareRouter->setBasePath($routePath); // setting base path for middleware router
        $routes = $middlewareRouter->getRoutesNoRegex();

        foreach ($routes as $http_method => $callables) {
          foreach ($callables as $path => $call) {
            // Getting path with without base path  
            $raw_path = $this->getRegexPattern($path);    

            $path = $this->getRegexPattern($routePath.$path);

            if (!isset($this->routes[$http_method][$path])){
                $this->routes[$http_method][$path] = $middlewareRouter->getRoutes()[$http_method][$raw_path];
              }

          }
        }
      } else {
        throw new \InvalidArgumentException("Error in use function");
      }
  }


}
?>