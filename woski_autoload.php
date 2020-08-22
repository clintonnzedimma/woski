<?php 
spl_autoload_register('__autoloadWoski');
spl_autoload_register('__autoloadApp');

function __autoloadWoski($class){
    $parts = explode('\\', $class);

   	if ($parts[0] === "Woski") {
   		unset($parts[0]);
   		$file =  __DIR__."/core/".implode("/", $parts).'.php';
      require $file;
   	}

}

function __autoloadApp($class){
    $parts = explode('\\', $class);
    if ($parts[0] === "App") {
       unset($parts[0]);
       $file =  __DIR__."/app/".implode("/", $parts).'.php';
      require $file;
    }

}