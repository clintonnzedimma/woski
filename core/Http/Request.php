<?php

namespace Woski\Http;

class Request 
{

    public $method;
    public $header;
    public $body;
    public $query;
    public $raw;
    public $files;
    public $cookies;
    
    public $session;
    
    function __construct()
    {
      if (isset($_SERVER)) {
          if (isset($_SERVER['REQUEST_METHOD'])) {
      
              $this->method = $_SERVER['REQUEST_METHOD'];
          }
          $this->header = $this->headers();

      }
      
      $this->raw = file_get_contents('php://input');
      if (isset($_POST)) {
          $this->body = $_POST;
      }
      if (isset($_GET)) {
          $this->query = $_GET;
      }
      if (isset($_FILES)) {
          $this->files = $_FILES;
      }
      if (isset($_COOKIE)) {
          $this->cookies = $_COOKIE;
      }
      if (isset($_SESSION)) {
            $this->session = $_SESSION;
     }


  }

    public static function file($key)
    {
        return $_FILES[$key] ?? null;
    }
    
    
    protected function headers()
    {
        $header  =  [];
        foreach ($_SERVER as $name  =>  $value) {
            if (preg_match('/^HTTP_/', $name)||preg_match('/^PHP_AUTH_/', $name)||preg_match('/^REQUEST_/', $name)) {
                $header[$name]  =  $value;
            }
        }
        return $header;
    }
    
    
    public function hasFile($key){
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

}
