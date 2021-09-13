<?php 
namespace Woski\Cli;
use Woski\Cli\Utils\ModelMaker;
 final class Cli{
	
	public function __construct()
	{
		$options = getopt(null,['run', 'port:', 'help', 'make:model:', 'table:']);

		if (isset($options['run']) && isset($options['port'])) {
			if (strlen($options['port']) < 4) {
				die("Invalid port length");
				exit;
			} elseif (strlen($options['port']) == 4) {
				echo "Your Woski App is running. Listening on http://localhost:".$options['port']." \n";
				echo "\n";			
				passthru("php -S localhost:".$options['port']);
			}
		}elseif (isset($options['run']) && !isset($options['port'])) {
			echo "Your Woski App is running. Listening on http://localhost:3030 \n";
			echo "\n";			
			 passthru("php -S localhost:3030");
		}


		if (isset($options['help'])) {
		    echo "Usage:  php woski [command] \n\n";

		    echo " \n";

		    echo "--run\tDisplays your application on the browser using default port 3030 \n\n";

		    echo "--run --port<number>\tDisplays your application on the port you choose\n";
		}




/*  
 This is creates a model class
 * sample command is
 * php woski --make:model [ModelName] --table [table_name]
 * 
*/

			if(isset($options['make:model'])){
				$modelMaker = new ModelMaker($options['make:model']);

				if(isset($options['table'])) {
					$modelMaker->create($options['table']);
				}
			

			}
		}

	}

?>