<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package Woski Controller
*/ 

namespace  Woski\Controller;
use ReflectionClass;
use ReflectionMethod;

abstract class WoskiController 
{

	function __construct()
	{
		$this->delcareMethodNamesAsProperty();
	}

	protected function delcareMethodNamesAsProperty()
	{
		$magic_methods = ["__construct", "__destruct", "__call"];	
		$class = new ReflectionClass($this);
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			if (!in_array($method->name, $magic_methods)) {
				$this->{$method->name} = $this->{$method->name}(); 
			}
		}
	}

}
 ?>