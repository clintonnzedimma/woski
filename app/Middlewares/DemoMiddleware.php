<?php
/**
 * A sample Middleware class
 */

namespace App\Middlewares;
use Woski\Controller\WoskiController;
use Woski\Support\Logger;

class DemoMiddleware extends WoskiController {
    public function handle() {
        return function ($req, $res, $pipe) {
             $logger = new Logger(); //logs to storage/logs/woski.log
             $logger->info("DemoMiddleware::handle called on {$req->method}");
             //return $pipe->block();  //uncomment to block the request and return a response
        };
   
    }
    
    public function foo() {
        return function ($req, $res, $pipe) {
             $logger = new Logger(); //logs to storage/logs/woski.log
             $logger->info("DemoMiddleware::foo called on {$req->method}");
            //  return $pipe->block();
        };
   
    }
}
