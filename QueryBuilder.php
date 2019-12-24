<?php 

require_once "MethodsAll.php";

class QueryBuilder extends MethodsAll { 

	static private $instance = null;
	
	private function __construct($db_name) {
		parent::connect($db_name);
	} 
 	
 	public static function getInstance() {
	 	if(self::$instance == null) 
	 	{
			self::$instance = new self($db_name);
		}
		return self::$instance;
	}
	
	public function table($name) {
		if(!in_array($name, $this->tables)) 
			throw new Exception("Таблица не найдена");

		$this->_t = $name;
		return $this;
	}

	public function limit(int $limit, int $offset = null) {
		$this->limit = [$limit, $offset];
		return $this;
	}

	public function join($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $this->mysqli->real_escape_string($field), $this->mysqli->real_escape_string($linkTo), $this->mysqli->real_escape_string($as));
		return $this;
	}

	public function leftJoin($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $this->mysqli->real_escape_string($field), $this->mysqli->real_escape_string($linkTo), $this->mysqli->real_escape_string($as), "LEFT");
		return $this;
	}

	public function rightJoin($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $this->mysqli->real_escape_string($field), $this->mysqli->real_escape_string($linkTo), $this->mysqli->real_escape_string($as), "RIGHT");
		return $this;
	}

	public function groupBy($field) {
		$this->groupBy[] = str_replace(".", "`.`", $this->mysqli->real_escape_string($field));
		return $this;
	}

	/*sort = "ACS" - standart, "DESC"*/
	public function sortAscDesc($field = "id", $sort = "ASC") {
		$this->order[] = [str_replace(".", "`.`", $this->mysqli->real_escape_string($field)), $this->mysqli->real_escape_string($sort)];
		return $this;
	}

	/*paramwhere = "" - standart, "AND", "OR"*/
	public function where($field, $znak, $val = null, $paramwhere = "") {
		$this->where[] = $this->_where($this->mysqli->real_escape_string($paramwhere), $this->mysqli->real_escape_string($field), $this->mysqli->real_escape_string($znak), $this->mysqli->real_escape_string($val));
		return $this;
	}

	/*paramhaving = "" - standart, "AND", "OR"*/
	public function having($field, $znak, $val = null, $paramhaving = "") {
		$this->having[] = $this->_where($this->mysqli->real_escape_string($paramwhere), $this->mysqli->real_escape_string($field), $this->mysqli->real_escape_string($znak), $this->mysqli->real_escape_string($val));
		return $this;
	}

	public function select() {
		return $this->_select($this->_t, $this->where, $this->join, $this->order, "*", $this->groupBy, $this->having, $this->limit);
	}

	public function delete() {
		return $this->_delete($this->_t, $this->where, $this->order, $this->limit);
	}

	public function insert($data) {
		return $this->_insert($this->_t, $data);
	}

	public function set($up_data) {
		$this->_set($up_data);
		return $this;
	}

	public function update() {

		return $this->_update($this->_t, $this->up_query, $this->where);
	}
	
}