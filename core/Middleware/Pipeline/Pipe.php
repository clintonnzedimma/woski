<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package Woski Pipeline
*/ 

namespace  Woski\Middleware\Pipeline;
use Woski\Middleware\Pipeline\Action\Next;
use Woski\Middleware\Pipeline\Action\Block;

class Pipe
{

	function __construct(){
		
	}


	public function next(){
		return new Next;
	}

	public function block()
	{
		return new Block;
	}


}
 ?>