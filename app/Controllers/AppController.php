<?php  
/**
 * A sample Controller class
 */
namespace App\Controllers;
use Woski\Controller\WoskiController as WoskiController;

class AppController extends WoskiController
{
	
	function __construct(){
		parent::__construct();
	}



	public function index() {
		return function ($req, $res) {
		   	 $res->render("index", 
		        [
		            "title"=>"Woski PHP"
		        ]);			
		};
	}


}

?>