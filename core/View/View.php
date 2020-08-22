<?php 
/**
 * @author Clinton Nzedimma
 */
 namespace Woski\View;

 use Exception;


class View
{
	private $data = [];

	private $render = null;

	public function __construct($template) {
	    try {
	        $file = ROOT . '/views/' . strtolower($template).'.php';

	        if (file_exists($file)) {
	            $this->render = $file;
	        } else {
	            throw new Exception('Woski View : ' . $template . ' not found!');
	        }
	    }
	    catch (Exception $e) {
	    	throw new Exception($e);
	    }
	}


	public function assign($vars){
		foreach ($vars as $key => $value) {
			  $this->data[$key] = $value;
		}
	}


	public function __destruct()
	{	
	    extract($this->data);
	    include($this->render);

	}
}

?>