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

  public function use($arg1, $arg2 = null, $arg3 = null) {
    // Case 1: Global middleware
    if ($arg2 === null) {
      if (is_callable($arg1)) {
          $this->middlewares[] = $arg1;
          return;
      }

      if (is_array($arg1)) {
          foreach ($arg1 as $mw) {
              if (is_callable($mw)) {
                  $this->middlewares[] = $mw;
              } else {
                  throw new \InvalidArgumentException("Global middleware must be callable");
              }
          }
          return;
      }
    }


    // Case 2: Prefix + Router only
    if (is_string($arg1) && $arg2 instanceof Router) {
        $prefix = $arg1;
        $router = $arg2;
        $router->setBasePath($prefix);

        $rawRoutes = $router->getRoutesNoRegex();
        foreach ($rawRoutes as $method => $routes) {
            foreach ($routes as $path => $callback) {
                $fullPath = $prefix . $path;
                $regex = $this->getRegexPattern($fullPath);
                $this->routes[$method][$regex] = $callback;
                $this->routes_no_regex[$method][$fullPath] = $callback;
            }
        }

        return;
    }

    // Case 3: Prefix + [middlewares] + Router
    if (is_string($arg1) && is_array($arg2) && $arg3 instanceof Router) {
        $prefix = $arg1;
        $middlewares = $arg2;
        $router = $arg3;

        $router->setBasePath($prefix);

        $rawRoutes = $router->getRoutesNoRegex();
        foreach ($rawRoutes as $method => $routes) {
            foreach ($routes as $path => $callback) {
                $fullPath = $prefix . $path;
                $regex = $this->getRegexPattern($fullPath);

                // Merge middleware with original callback(s)
                $handlers = array_merge($middlewares, is_array($callback) ? $callback : [$callback]);

                $this->routes[$method][$regex] = $handlers;
                $this->routes_no_regex[$method][$fullPath] = $handlers;
            }
        }

        return;
    }

    throw new \InvalidArgumentException("Invalid arguments passed to use()");
    
  
}




}
