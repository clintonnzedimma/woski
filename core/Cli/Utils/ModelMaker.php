<?php 
/**
 * Woski - A simple PHP framework
 * @author Clinton Nzedimma <clinton@woski.xyz>
 * @package Woski CLI
 * This is responsible for generating model classes
*/ 


namespace Woski\Cli\Utils;
use \PDO;

class ModelMaker 
{

    private $pdo;
    private $modelName;
    private $table;
   

    function __construct($modelName)
    {   
        $this->modelName = $modelName;
    }


    private function dbConnect(){
        $_CONFIG = _import("config/db.php");
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

        }catch(\Exception $e){
            die($e->getMessage());
        }
    }


    public function create($table){
        $this->table = $table;
        $this->dbConnect();

        $fields = array_map(function($f){
            return "'$f'";
        },$this->getTableFields());

        $fields = implode(",", $fields);

        $myfile = fopen("app/Models/$this->modelName.php", "w") or die("Unable to open file!");
        $txt =
"<?php
namespace App\\\Models;
use Woski\\\ORM\\\WoskiModel; 
class $this->modelName extends WoskiModel
{   
    public \$table = '$this->table';

    public \$pk = 'id';
    
    public \$fields = [$fields ];
}
";
        $data = stripslashes($txt);
        fwrite($myfile, $data);
        fclose($myfile);
        
        echo "WoskiPHP: [".$this->modelName."] database model created successfully";
    }


    protected function getTableFields() {
        $sql = "DESCRIBE $this->table";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_COLUMN);
  }
}