<?php 
spl_autoload_register('__autoloadWoski');
spl_autoload_register('__autoloadApp');
spl_autoload_register('__autoloadTests');

// Autoload Woski Core

function __autoloadWoski($class){
    $parts = explode('\\', $class);

   	if ($parts[0] === "Woski") {
   		unset($parts[0]);
   		$file =  __DIR__."/core/".implode("/", $parts).'.php';
      require $file;
   	}
}

// Autoload Woski App

function __autoloadApp($class){
    $parts = explode('\\', $class);
    if ($parts[0] === "App") {
       unset($parts[0]);
       $file =  __DIR__."/app/".implode("/", $parts).'.php';
      require $file;
    }
}

// Autoload Tests

function __autoloadTests($class){
    $parts = explode('\\', $class);
    if ($parts[0] === "Tests") {
       unset($parts[0]);
       $file =  __DIR__."/tests/".implode("/", $parts).'.php';
      require $file;
    }
}