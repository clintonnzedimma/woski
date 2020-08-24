<?php  
/**
 * A sample Controller class
 */
namespace App\Controllers;
use Woski\Controller\WoskiController;

class AppController extends WoskiController
{
	

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