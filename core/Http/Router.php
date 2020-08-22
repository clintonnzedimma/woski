<?php
/**
 * Woski - A simple PHP framework
 * @author Clinton Nzedimma <clinton@woski.xyz>
 * @package Woski HTTP
*/ 

namespace Woski\Http;
use Exception;
use InvalidArgumentException;

use Woski\Http\StaticHandler;
use Woski\Middleware\Pipeline\Pipe;
use Woski\Middleware\Pipeline\Action\Next;
use Woski\Middleware\Pipeline\Action\Block;

class Router
{
    //Method of Request
  protected $method =  null;
  //Route Array
  protected $routes =  [];
  //Callback error function
  protected $errorFunction = null;
  //Request Array to store Request related information
  protected $request  =  null;
  //Current Request PATH
  protected $currentPath = null;
  //Object of class Response
  protected $response = null;
  //Regex Allowed Characters
  protected $CharsAllowed  =  '[a-zA-Z0-9\_\-]+';

  protected $CharsForQuery = '[a-zA-Z0-9\_\-\.\?\=\&]+';

  protected $validRoute  =  false;

  protected $middlewares = [];

  protected $base_path;

  protected $routes_no_regex = [];

  protected $pipe;



  /**
   * Default Constructor: Initalizing all variables
   * @method __construct
   */
  public function __construct()
  {
      if (isset($_SERVER)) {
          if (isset($_SERVER['REQUEST_METHOD'])) {
              $this->method  =  $_SERVER['REQUEST_METHOD'];
              $this->request["method"] = $_SERVER['REQUEST_METHOD'];
          }
          $this->request["header"] = $this->getHTTPHeaders();

          //default is 
          if (isset($_SERVER['REQUEST_URI'])) {
              $this->currentPath  =  strtok($_SERVER["REQUEST_URI"], '?'); // removing query string from request URI
          }
      }
      if (isset($_POST)) {
          $this->request["body"] = $_POST;
          $this->request["raw"] = file_get_contents('php://input');
      }
      if (isset($_GET)) {
          $this->request["query"] = $_GET;
      }
      if (isset($_FILES)) {
          $this->request["files"] = $_FILES;
      }
      if (isset($_COOKIE)) {
          $this->request["cookies"] = $_COOKIE;
      }

      $this->request['params'] = [];
      $this->response  =  new Response();
      $this->pipe = new Pipe;
      $this->routes = ['GET' =>[],'POST' =>[],'PUT' =>[],'DELETE' =>[],'PATCH' =>[]];
      $this->routes_no_regex = ['GET' =>[],'POST' =>[],'PUT' =>[],'DELETE' =>[],'PATCH' =>[]];

  }


/**
 * Function to get headers related to HTTP,PHP_AUTH and REQUEST from $_SERVER
 * @method getHTTPHeaders
 * @return Array         returns an containing all information related to HTTP,PHP_AUTH and REQUEST from $_SERVER
 */
protected function getHTTPHeaders()
{
    $header  =  [];
    foreach ($_SERVER as $name  =>  $value) {
        if (preg_match('/^HTTP_/', $name)||preg_match('/^PHP_AUTH_/', $name)||preg_match('/^REQUEST_/', $name)) {
            $header[$name]  =  $value;
        }
    }
    return $header;
}

/**
 * Turns given path into regular expression for comparison in complex routing
 * @method getRegexPattern
 * @param  string                 $path Route
 * @return string                       Turns route into a regex
 */
protected function getRegexPattern($path)
{
    //Check for invalid pattern
  if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $path)) {
      return false;
  }
    // Turn "(/)" into "/?"
    $path  =  preg_replace('#\(/\)#', '/?', $path);

     //Replace parameters
    $path  =  preg_replace('/:(' . $this->CharsAllowed . ')/', '(?<$1>' . $this->CharsAllowed . ')', $path);


    $path  =  preg_replace('/{(' . $this->CharsAllowed . ')}/', '(?<$1>' . $this->CharsAllowed . ')', $path);

    // Add start and end matching
    $patternAsRegex  =  "@^" . $path . "$@D";
    return $patternAsRegex;
}


  /**
   * Add the given route to 'GET' array for lookup
   * @method get
   * @param  string   $path     Route
   * @param  function $callback Function to be called when the current equates the provided route; The callback must take request array and response object as parameters
   * @return void
   */
  public function get($path, $callback){    
        $this->routes_no_regex['GET'][$path] = $callback;
        $this->routes['GET'][$this->getRegexPattern($path)] = $callback;
  }
  /**
   * Add the given route to 'POST' array for lookup
   * @method post
   * @param  string   $path     Route
   * @param  function $callback Function to be called when the current equates the provided route; The callback must take request array and response object as parameters
   * @return void
   */
  public function post($path, $callback){   
      $this->routes_no_regex['POST'][$path] = $callback;
      $this->routes['POST'][$this->getRegexPattern($path)] = $callback;
  }
  /**
   * Add the given route to 'PUT' array for lookup
   * @method put
   * @param  string   $path     Route
   * @param  function $callback Function to be called when the current equates the provided route; The callback must take request array and response object as parameters
   * @return void
   */
  public function put($path, $callback) {
      $this->routes_no_regex['PUT'][$path] = $callback;
      $this->routes['PUT'][$this->getRegexPattern($path)] = $callback;
  }
  /**
   * Add the given route to 'PATCH' array for lookup
   * @method patch
   * @param  string   $path     Route
   * @param  function $callback Function to be called when the current equates the provided route; The callback must take request array and response object as parameters
   * @return void
   */
  public function patch($path, $callback){ 
      $this->routes_no_regex['PATCH'][$path] = $callback;
      $this->routes['PATCH'][$this->getRegexPattern($path)] = $callback;
  }
  /**
   * Add the given route to 'DELETE' array for lookup
   * @method delete
   * @param  string   $path     Route
   * @param  function $callback Function to be called when the current equates the provided route; The callback must take request array and response object as parameters
   * @return void
   */
  public function delete($path, $callback){   
      $this->routes_no_regex['PATCH'][$path] = $callback;
      $this->routes['DELETE'][$this->getRegexPattern($path)] = $callback;
  }
  /**
   * Error Handler to set handler to be called when no routes are found
   * @method error
   * @param  function $function A callback function that takes request array and response object
   * @return void
   */
  public function error($method, $function)
  {

    if ($_SERVER['REQUEST_METHOD']  ==  $method && !$this->validRoute) {
      $this->errorFunction = $function;
     } 
  }

  /**
   * Function to get appropriate callback for the current PATH_INFO based on REQUEST_METHOD
   * @method getCallback
   * @param  string        $method REQUEST_METHOD as string
   * @return function              The callback function
   */
  public function getCallback($method)
  {
      if (!isset($this->routes[$method])) {
          return null;
      }

      foreach ($this->routes[$method] as $name  =>  $value) {
          if (preg_match($name, $this->currentPath, $matches)||preg_match($name, $this->currentPath."/", $matches)) {
              // Get elements with string keys from matches
             $params  =  array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));

              foreach ($params as $key =>  $value) {
                  $this->request["params"][$key] = $value;
              }


              return $this->routes[$method][$name];
          }
      }
  }




  public function objectifyRequest () {
    if ($this->request) {
        $this->request["session"]  =  (object) $_SESSION;
        $this->request["params"]  = (object) $this->request["params"];
        $this->request["query"] = (object) $_GET;
        $this->request = (object) $this->request;
    }
  }


  protected function dispatchMiddlewares(){
    if ($this->middlewares) {
      foreach ($this->middlewares as $middleware) { 
        $fn = $middleware($this->request, $this->response,$this->pipe);

        if ($fn instanceof Next) {
           continue;
        }else if ($fn instanceof Block) {
          return;
        }
      }
    }
  }


  /**
   * Starts the routing process by matching current PATH_INFO to avaialable routes in array $routes
   * @method start
   * @return function  Returns callback function of the appropriate route or returns callback function of the error handler
   */
  public function start()
  {   
      $callback = $this->getCallBack('ANY');;
      if ($callback) {
          $this->objectifyRequest();
          $this->dispatchMiddlewares();
          return $callback($this->request, $this->response, $this->pipe);
      }
      $callback = $this->getCallBack($this->method);
      if ($callback) {
          $this->objectifyRequest();
          $this->dispatchMiddlewares();

          if (is_array($callback)) {
            $count = 0;
            foreach ($callback as $call) {
              $count ++;
              $fn = $call($this->request, $this->response, $this->pipe);

              if ($fn instanceof Next) {
                 continue;
              }else if ($fn instanceof Block) {
                return;
              }

              if ($count == count($callback)) return;
            }
          }
          return $callback($this->request, $this->response, $this->pipe);
      }
      if (isset($this->errorFunction)) {
          return ($this->errorFunction)(new Exception("Path not found!", 404), $this->response);
      }
  }



  public function setBasePath($path) {
    return $this->base_path = $path;
  }

  public function getCurrentPath()
  {
    return $this->currentPath;
  }

  public function getBasePath()
  {
    return $this->base_path;
  }


  public function getRoutes($method = null)
  {
    if ($method == null) {
     return $this->routes;
    }

    if (in_array($method,array_keys($this->routes))) {
      return $this->routes[$method];
    }

    return null;
  }


  public function getRoutesNoRegex($method = null)
  {
    if ($method == null) {
     return $this->routes_no_regex;
    }

    if (in_array($method,array_keys($this->routes_no_regex))) {
      return $this->routes_no_regex[$method];
    }

    return null;
  }



  

}
?>