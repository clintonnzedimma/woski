<?php 
/**
 * Woski - A simple PHP framework
 * @author Clinton Nzedimma <clinton@woski.xyz>
 * @package Woski HTTP
*/ 


namespace Woski\Http;


use InvalidArgumentException;
use Woski\Config\Handler;
use Woski\View\View;



class Response {
  public $static_dir = '';

  private $config_handler;


  public function __construct() {
    $this->config_handler = new Handler;

  }

  public function sendStatus($status) {
    http_response_code($status);
  }


  public function json($json, $status = null) {
    header('Content-Type: application/json');
    if (is_array($json)) echo json_encode($json);
    else if (is_string($json) && json_decode($json)) echo $json;
    else echo json_encode(['error' => 'Invalid JSON format']);
    if ($status) http_response_code($status);
  }
  

  public function setHeader() {
    $args = func_get_args();
    if (count($args) == 1 && is_array($args)) {
      foreach ($args[0] as $header => $value) {
        header("$header: $value");
      }
    } else if (count($args) == 2) header("$args[0]: $args[1]");
  }


  public function sendFile($file, $vars = null) {
    if (is_array($vars)) extract($vars);
    if (strpos($file, '/') === 0 || strpos($file, './') === 0 || strpos($file, '../') === 0) include $file;
    else include $file;
  }




  public function redirect($url) {
    $new_location = APP_URL . '/' . trim($url, '/');
    header("Location: $new_location");
  }

  public function render ($template, $vars = []){
      $view = new View($template);

      if (array_key_exists('_APP_GLOBALS',$vars)) {
        throw new InvalidArgumentException("You cant use '_APP_GLOBALS' as variable, it is a Woski Reserved Keyword ");
      }

      $vars['_APP_GLOBALS'] = $this->config_handler->globals;
      $view->assign($vars);
  } 
}

?>