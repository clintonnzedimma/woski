<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package ORM
*/ 


namespace Woski\ORM;

use Woski\Database\AbstractDatabase;
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



	function __construct()
	{
		 parent::__construct();
		 $this->fieldsCompare();
		 $this->validatePK();
	}



	protected function getTableFields() {
		$result = $this->pdo->query("SELECT * FROM $this->table");
		return array_keys($result->fetch(PDO::FETCH_ASSOC));
	}


	protected function fieldsCompare(){
		$invalid_table_fields = array_diff($this->fields, $this->getTableFields());
		if (count($invalid_table_fields) > 0) {
			throw new Exception("The field [".implode(", ",$invalid_table_fields)."] are not in the '$this->table' table", 1);
		}
	}


	protected function validatePK(){
		$is_validated = array_search($this->pk, $this->fields);

		if (!$this->pk) throw new Exception("WoskiORM: Please set primary key "); 

		if($is_validated === false) throw new Exception("WoskiORM: Field '$this->pk' does not exists in the fields you declared"); 
	}


    private function values()
    {
        foreach ($this->data as $k => $v) {
            $values[] = ":{$k}";
        }

        return implode(',', $values);
    }



  private function where($separator = "AND")
  {
        return $this->where = (isset($this->data['conditions']))
                              ? 'WHERE ' . self::conditions($separator)
                              : '';
    }


   private function conditions($separator)
    {
        $param = [];
        foreach ($this->data['conditions'] as $k => $v) {
            $param[] = "{$k} = :{$k}";
        }

        return implode($separator, $param);
    }



    private function param($data = null)
    {
        if (empty($data)) {
            $data = $this->data['conditions'];
        }

        foreach ($data as $k => $v) {
            $check_val = (is_int($v)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->stmt->bindValue(":{$k}", $v, $check_val);
        }
    }




    private function fields($data = null) :string {
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


    private function insertQueryString() :string{
        $fields = self::fields($this->data);
        $values = self::values();
        return "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
    }





	public function create($data) {
		$this->data = $data;

		$this->stmt = $this->pdo->prepare(self::insertQueryString());
		self::param($data);

		$exec = $this->stmt->execute();

		$this->count = $this->stmt->rowCount();

		return (object) [
			"result" => $exec
		];

	}


    public function delete($data, $separator = "AND")
    {
        $this->data['conditions'] = $data;

        $sql = "DELETE FROM {$this->table} " . self::where($separator);
        $this->stmt = $this->pdo->prepare($sql);

        if ( ! empty($this->where)) {
            self::param();
        }

        $exec = $this->stmt->execute();
        $this->count = $this->stmt->rowCount();

       return (object) [
       		"result" => $exec
       ];       
    }


   public function update($data)
    {
        if (! array_key_exists($this->pk, $data)) {
            throw new InvalidArgumentException("WoskiORM: The primary key '$this->pk' not set in the update query ", 1);
        }

        $param   = $data;
        $this->where = self::updateWhere($data);
        $this->stmt  = $this->pdo->prepare(self::updateQueryString($data));
        self::param($param);
        $exec = $this->stmt->execute();
        $this->count = $this->stmt->rowCount();

       	return (object) [
       		"result" => $exec
       ];             
    }


    private function updateQueryString($data)
    {
        $this->data['conditions'] = $data;
        $fields = self::conditions(',');
        return "UPDATE {$this->table} SET {$fields} {$this->where}";
    }



    private function updateWhere($data)
    {
        $this->data['conditions'] = [$this->pk => $data[$this->pk]];
        $where = 'WHERE ' . self::conditions('');
        unset($data[$this->pk]);
        return $where;
    }


    private function find()
    {
        $sql = "SELECT " . self::fields() . " FROM {$this->table} " . self::where();

        $this->stmt = $this->pdo->prepare($sql);

        if ( ! empty($this->where)) {
            self::param();
        }

        $this->stmt->execute();
        return $this;
    }


    public function fetchAll($data = null)
    {
        $this->data['conditions'] = $data;
        return $this->find()->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne($data)
    {
        $this->data['conditions'] = $data;
        return $this->fetch = $this->find()->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchByPK($pk)
    {
        return self::fetchOne([$this->pk => $pk]);
    }

    public function exists($pk){
        if (is_array($pk)) {
            return (self::fetchOne($pk));
        }
        return (self::fetchByPK($pk));
    }

}

?>