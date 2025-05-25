<?php

namespace App\Middlewares;

class LoggerMiddleware {

    public function handle() {
        return function ($req, $res, $pipe) {
         // return this if you want block the request
        // return $pipe->block();
        };
    }
}