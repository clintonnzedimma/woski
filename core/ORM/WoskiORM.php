<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package ORM
*/ 


namespace Woski\ORM;

use Woski\Database\AbstractDatabase;
use ReflectionClass;
use Exception;
use InvalidArgumentException;
use PDO;

abstract class WoskiORM extends AbstractDatabase
{
    protected $table;

    protected $fields = [];

    protected $sql;

    protected $pk;

    private $where;

    private $sort_data = null;
    
    private $reflection;
    


    function __construct()
    {
         parent::__construct();
         $this->fieldsCompare();
         $this->validatePK();  
         $this->reflection = new ReflectionClass($this) ;
    }



  /**
   * This method get table fields
   * @method getTableFields
   * @return array
   */
    protected function getTableFields() {
        $result = $this->pdo->query("SELECT * FROM $this->table");
        return array_keys($result->fetch(PDO::FETCH_ASSOC));
    }


  /**
   * This method validates compare $fields and actual table fields
   * @method fieldsCompare
   * @return null
   */
    protected function fieldsCompare(){
        $invalid_table_fields = array_diff($this->fields, $this->getTableFields());
        if (count($invalid_table_fields) > 0) {
            throw new Exception("WoskiORM: The field [".implode(", ",$invalid_table_fields)."] are not in the '$this->table' table  in {$this->reflection->getShortName()} model");
        }
    }

  /**
   * This method validates primary key
   * @method validatePK
   * @return null
   */
    protected function validatePK(){
        $is_validated = array_search($this->pk, $this->fields);

        if (!$this->pk) throw new Exception("WoskiORM: Please set primary key in {$this->reflection->getShortName()} model"); 

        if($is_validated === false) throw new Exception("WoskiORM: Field '$this->pk' does not exists in the fields declared  in {$this->reflection->getShortName()} model"); 
    }

  /**
   * This method prepares values for binding
   * @method value
   * @return string
   */
    private function values()
    {
        foreach ($this->data as $k => $v) {
            $values[] = ":{$k}";
        }

        return implode(',', $values);
    }


  /**
   * This method generates SQL condition string
   * @method where
   * @return string
   */
    private function where($separator = "AND")
    {
        return $this->where = (isset($this->data['conditions']))
                                ? 'WHERE ' . self::conditions($separator)
                                : '';
        }


        
  /**
   * This method prepares condition data for binding
   * @method conditions
   * @return string
   */
   private function conditions($separator)
    {
        $param = [];
        foreach ($this->data['conditions'] as $k => $v) {
            $param[] = "{$k} = :{$k}";
        }

        return implode($separator, $param);
    }


    
  /**
   * This method helps bind params
   * @method bindParam
   * @return null
   */
    private function bindParam($data = null)
    {
        if (empty($data)) {
            $data = $this->data['conditions'];
        }

        foreach ($data as $k => $v) {
            $check_val = (is_int($v)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->stmt->bindValue(":{$k}", $v, $check_val);
        }
    }


 /**
   * This implodes set fields for a SELECT statement
   * @method fields
   * @return string
   */

    private function fields($data = null) {
        if (empty($data) && isset($this->data['fields'])) {
            return implode(',', $this->data['fields']);
        }

        if ( ! empty($data)) {
            foreach ($data as $k => $v) {
                $fields[] = $k;
            }
            return implode(',', $fields);
        }

        return '*';
    }

/**
   * This method helps generates INSERT SQL
   * @method insertQueryString
   * @return string
   */
    private function insertQueryString() :string{
        $fields = self::fields($this->data);
        $values = self::values();
        return "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
    }

 /**
   * This method creates entry for a model in the database
   * @method create
   * @return object
   */
    public function create($data) {
        $this->data = $data;
        $this->stmt = $this->pdo->prepare(self::insertQueryString());
        self::bindParam($data);

        $exec = $this->stmt->execute();

        $this->count = $this->stmt->rowCount();

        return (object) [
            "result" => ($this->count > 0) ? true : false,
            "status" => $exec
        ];
    }
    


 /**
   * This deletes entry in the database
   * @method delete
   * @return string
   */

    public function delete($data, $separator = "AND")
    {
        $this->data['conditions'] = $data['WHERE'];

        $sql = "DELETE FROM {$this->table} " . self::where($separator);
        $this->stmt = $this->pdo->prepare($sql);

        if ( ! empty($this->where)) {
            self::bindParam();
        }

        $exec = $this->stmt->execute();
        $this->count = $this->stmt->rowCount();

        return (object) [
            "result" => ($this->count > 0) ? true : false,
            "status" => $exec
        ];       
    }

 /**
   * This entry entry in the database
   * @method update
   * @return string
   */
   public function update($data, $where)
    {
        $param = $data;

        if (!$where) {
            throw new InvalidArgumentException("WoskiORM: No WHERE parameters given");
        } else if(!is_array($where) || !array_key_exists('WHERE', $where)) {
            throw new InvalidArgumentException("WoskiORM: Parameter 2 invalid");     
        }

        $this->where = self::updateWhere($where)->str;
        $this->stmt  = $this->pdo->prepare(self::updateQueryString($data));

        foreach (self::updateWhere($where)->arr as $key => $value) {
            $param[$key] = $value;
         }

        self::bindParam($param);
        $exec = $this->stmt->execute();
        $this->count = $this->stmt->rowCount();

        return (object) [
               "result" => ($this->count > 0) ? true : false,
               "status" => $exec
       ];             
    }



 /**
   * This method helps generates UPDATE SQL
   * @method updateQueryString
   * @return string
   */
    private function updateQueryString($data)
    {
        $this->data['conditions'] = $data;
        $fields = self::conditions(',');
        return "UPDATE {$this->table} SET {$fields} {$this->where}";
    }


 /**
   * This method helps WHERE condition context
   * @method updateWhere
   * @return object
   */
    private function updateWhere($param)
    {
        $this->data['conditions'] = $param['WHERE'];
        $string = 'WHERE ' . self::conditions('  AND   ');
        return (object) [
             "str" => $string,
             "arr" => $param['where'] 
        ];
    }

 /**
   * This method finds data for model in the database
   * @method find
   * @return object
   */
    private function find()
    {
        $this->validateSortData();

        $sql = "SELECT " . self::fields() . " FROM {$this->table} " . self::where()." ". $this->structureSortString();

        $this->stmt = $this->pdo->prepare($sql);

        if (!empty($this->where)) {
            self::bindParam();
        }

        $this->stmt->execute();

        $this->sort_data = null;

        return $this;
    }



 /**
   * This method validates $sort_data for find query
   * @method validateSortData
   * @return null
   */  
    private function validateSortData() 
    {   
        $allowed_sort_keys = [
            "ORDER_BY", 
            "LIMIT", 
            "ASC",
            "DESC"
        ];

        if($this->sort_data != null) {
            if(!is_array($this->sort_data)) {
                throw new InvalidArgumentException("WoskiORM: Sorting parameter should be an associative array"); 
            }else if(is_array($this->sort_data)) {
                $sort_keys = array_keys($this->sort_data);

                $invalid_keys = array_diff($sort_keys, $allowed_sort_keys);

                foreach ($invalid_keys as $key => $value) {
                    throw new InvalidArgumentException("WoskiORM: Sort key {$value} is invalid"); 
                }

                if(count($invalid_keys) == 0) {
                    if(isset($this->sort_data['ORDER_BY'])){
                        if(!in_array($this->sort_data['ORDER_BY'], $this->fields)) {
                            throw new InvalidArgumentException("WoskiORM: Your ORDER_BY sort value of `{$this->sort_data['ORDER_BY']}` does not exist in fields declared in {$this->reflection->getShortName()} model");
                        }
                    }

                    if(isset($this->sort_data['LIMIT'])){
                        if(!is_int($this->sort_data['LIMIT']) 
                            && !is_array($this->sort_data['LIMIT'])){
                            throw new InvalidArgumentException("WoskiORM: Your LIMIT sort value must be an integer or array ");  
                        } else if (is_int($this->sort_data['LIMIT'])
                             && $this->sort_data['LIMIT'] <= 0){
                            throw new InvalidArgumentException("WoskiORM: Your LIMIT sort value must be an integer greater 0");  
                        } else if (is_array($this->sort_data['LIMIT'])){
                            if(count($this->sort_data['LIMIT']) != 2) {
                                throw new InvalidArgumentException("WoskiORM: Your LIMIT sort values array is invalid");  
                            } else if(count($this->sort_data['LIMIT']) == 2) {
                                 $i=0;
                                 foreach ($this->sort_data['LIMIT'] as $key => $value) {
                                     $i++;
                                     if(!is_int($value)) {
                                        throw new InvalidArgumentException("WoskiORM: Your LIMIT sort value at position $i is not an integer");
                                     }
                                     if(is_int($value) && $value < 0) {
                                        throw new InvalidArgumentException("WoskiORM: Your LIMIT sort value must be an integer  greater or equal to 0 at position $i");
                                     }

                                 }
                            }
                        }
                    }

                    if(isset($this->sort_data['DESC']) && isset($this->sort_data['ASC'])) {
                        if($this->sort_data['DESC'] === true 
                            &&
                            $this->sort_data['DESC'] === $this->sort_data['ASC']
                        ) {
                            throw new InvalidArgumentException("WoskiORM: Your ASC & DESC sort values cannot be both boolean TRUE "); 
                        }
                    }
                    
                    if(isset($this->sort_data['DESC']) && !is_bool($this->sort_data['DESC'])) {
                        throw new InvalidArgumentException("WoskiORM: Your DESC sort value must be a boolean");      
                    }
 
                    if(isset($this->sort_data['ASC']) && !is_bool($this->sort_data['ASC'])) {
                        throw new InvalidArgumentException("WoskiORM: Your ASC sort value must be a boolean");      
                    }

                }

            }
        }
    } 



 /**
   * This structures stort string for find query condition
   * @method structureSortString
   * @return null
   */      
    private function structureSortString()
     {
        $s = '';

        if (isset($this->sort_data['ORDER_BY'])) {
            $s .= "ORDER BY {$this->sort_data['ORDER_BY']} ";
        }

        if(isset($this->sort_data['DESC'])){
            $s.= "DESC ";
        }

        if(isset($this->sort_data['LIMIT'])){
            if(is_int($this->sort_data['LIMIT'])) 
                $s.= "LIMIT {$this->sort_data['LIMIT']}";

            if(is_array($this->sort_data['LIMIT']))
                 $s.= "LIMIT ". implode(", ", $this->sort_data['LIMIT']);
        }

        return $s;
    } 




 /**
   * This finds data in the database
   * @method findAllWhere
   * @return array
   */
    public function findAllWhere($data = null, $sort = null)
    {
        $this->data['conditions'] = $data;
        $this->sort_data = $sort;
        return $this->find()->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 /**
   * This finds only one data in the database
   * @method findOneWhere
   * @return array
   */
    public function findOneWhere($data)
    {
        $this->data['conditions'] = $data;
        return $this->fetch = $this->find()->stmt->fetch(PDO::FETCH_ASSOC);
    }


 /**
   * This finds only one data in the database by primary key
   * @method findByPK
   * @return array
   */
    public function findByPK($pk)
    {
        return self::findOneWhere([$this->pk => $pk]);
    }


  /**
   * This checks if primary key data exists
   * @method exists
   * @return boolean
   */   
    public function exists($pk){
        if (is_array($pk)) {
            return (self::findOneWhere($pk));
        }
        return (self::findByPK($pk));
    }

}

?>