<?php 
function _import($file){
	if (!file_exists($file)) {
		throw new \Exception("Woski: Cant Import File '$file' to your project");
	}
	return require_once $file;
 }

 function __stop(){
 	return ;
 }
?>