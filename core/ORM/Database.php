<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package ORM
*/ 



namespace Woski\ORM;

use \PDO;

abstract class Database
{
    protected  $pdo;

	 public function __construct()
	    {
	    	$_CONFIG = _import(ROOT."/config/db.php");

	        try{
	          switch ($_CONFIG['DB_CONNECTION']) {
	            case 'mysql':
	            $this->pdo = new PDO('mysql:host='.$_CONFIG['DB_HOST'].';dbname='.$_CONFIG['DB_NAME'],$_CONFIG['DB_USERNAME'],$_CONFIG['DB_PASSWORD']);

	          	$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

	              break;

	            case 'pgsql':
	          	 $this->pdo = new PDO('pgsql:host='.$_CONFIG['DB_HOST'].''.$_CONFIG['DB_PORT'].'dbname='.$_CONFIG['DB_NAME'],$_CONFIG['DB_USERNAME'],$_CONFIG['DB_PASSWORD']);

	            $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

	            break;

	            default:
	            
	              break;
	          }

	      }catch(PDOEXception $e){
	          die($e->getMessage());
	      }
	    }
}

?>