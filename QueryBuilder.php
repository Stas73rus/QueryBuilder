<?php 

require_once "MethodsAll.php";

class QueryBuilder extends MethodsAll { 

	static private $instance = null;
	

	private function __construct() {
		parent::connect();
	} 
 	
 	public static function getInstance() {
	 	if(self::$instance == null) 
	 	{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __get($name)
	{
		if(!in_array($name, $this->tables)) 
			throw new Exception("Таблица не найдена");

		$this->_t = $name;
		return $this;
	}
	
	protected function _select($table, $where = [], $join = null, $order = null, 
		$fields = "*", $group_by = null, $having = null, $limit = null) {
		$query = "SELECT {$fields} FROM `{$table}`";
		/*join*/
		if (!empty($join)) {
			foreach ($join as $value) {
				$query .= " {$value[0]} JOIN `{$value[1]}`";
				if(!empty($value[2]))
					$query .= " AS `{$value[2]}`";
				$query .= " ON ({$value[3]})";
			}
		}

		/*$where*/
		if (!empty($where)) {
			$query .= " WHERE";
			foreach ($where as $value) {
				$query .= " {$value[0]} ";
				if(count($value) > 1) {
					$query .= "( `{$value[1]}` {$value[2]} {$value[3]} )";
				}	
			}
		}

		/*$group_by*/
		if (!empty($group_by)) {
			$query .= " GROUP BY (`" . implode("`,`", $group_by) . "`)";
		}

		/*$having*/
		if (!empty($having)) {
			$query .= " HAVING";
			foreach ($having as $value) {
				$query .= " {$value[0]} ";
				if(count($value) > 1) {
					$query .= "( `{$value[1]}` {$value[2]} {$value[3]} )";
				}	
			}
		}

		/*$order*/
		if (!empty($order)) {
			$query .= " ORDER BY";
			$temp = [];
			foreach ($order as $value) {
				$temp[] = " `{$value[0]}` {$value[1]}";
			}
			$query .= implode(",", $temp);
		}

		/*$limit*/
		if (!empty($limit)) {
			$query .= " LIMIT {$limit[0]}";
			if(!empty($limit[1]))
				$query .= " OFFSET {$limit[1]}";
		}

		return $query;
	}

	public function limit(int $limit, int $offset = null) {
		$this->limit = [$limit, $offset];
		return $this;
	}

	protected function _join($table, $field, $linkTo = null, $as = null,  $type = "INNER") {
		

		if($linkTo === null)
			$linkTo = "{$this->_t}`.`id";
		else
			$linkTo = str_replace(".", "`.`", $linkTo);
		
		$on = "";
		if(empty($as)) 
			$on = "`{$table}`";
		else 
			$on = "`{$as}`";
		$on .= ".`{$field}` = `{$linkTo}`";

		//$on = (empty($as) ? "`{$table}`" : "`{$as}`") . ".`{$field}` = {$linkTo}";

		$this->join[] = [$type, $table, $as, $on];
	}

	public function join($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $field, $linkTo, $as);
		return $this;
	}

	public function leftJoin($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $field, $linkTo, $as, "LEFT");
		return $this;
	}

	public function rightJoin($table, $field, $linkTo = null, $as = null) {
		$this->_join($table, $field, $linkTo, $as, "RIGHT");
		return $this;
	}

	public function groupBy($field) {
		$this->groupBy[] = str_replace(".", "`.`", $field);
		return $this;
	}

	public function asc($field = "id") {
		$this->order[] = [str_replace(".", "`.`", $field), "ASC"];
		return $this;
	}

	public function desc($field = "id") {
		$this->order[] = [str_replace(".", "`.`", $field), "DESC"];
		return $this;
	}

	public function where($field, $znak, $val = null) {
		$this->where[] = $this->_where("", $field, $znak, $val);
		return $this;
	}

	public function andWhere($field, $znak, $val = null) {
		$this->where[] = $this->_where("AND", $field, $znak, $val);
		return $this;
	}

	public function orWhere($field, $znak, $val = null) {
		$this->where[] = $this->_where("OR", $field, $znak, $val);
		return $this;
	}

	public function having($field, $znak, $val = null) {
		$this->having[] = $this->_where("", $field, $znak, $val);
		return $this;
	}

	public function andHaving($field, $znak, $val = null) {
		$this->having[] = $this->_where("AND", $field, $znak, $val);
		return $this;
	}

	public function orHaving($field, $znak, $val = null) {
		$this->having[] = $this->_where("OR", $field, $znak, $val);
		return $this;
	}

	protected function _where($type, $field, $znak, $val) {
		if($val === null) {
			$val = $znak;
			$znak = "=";
		}

		if(!is_integer($val) && $val[0] != ":" && $val[0] != "?")
			$val = $this->dbh->quote($val);
		return [$type, str_replace(".", "`.`", $field), $znak, $val];
	}

	public function select() {
		return $this->_select($this->_t, $this->where, $this->join, $this->order, "*", $this->groupBy, $this->having, $this->limit);
	}

	
}