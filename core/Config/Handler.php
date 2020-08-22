<?php 
namespace Woski\Config;

class Handler{
	
	public $globals;

	function __construct () {
		$this->globals = _import(ROOT.'/config/tpl_globals.php');
	}
}

?>