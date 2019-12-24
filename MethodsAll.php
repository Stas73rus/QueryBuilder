<?php

require_once "Config.php";

abstract class MethodsAll {
	
	protected $dbh; 
	//все таблицы
	protected $tables = ["users", "users1"];
	//текущая таблица
	protected $_t = [];

	protected $mysqli;

	protected $where = [];
	protected $order = [];
	protected $join = [];
	protected $groupBy = [];
	protected $having = [];
	protected $limit = null;

	protected $up_query;

	private $znak_s = array (">", "<", ">=", "<=", "!=", "<>", "=");
	private $vubor = array ("AND", "OR", "");


	protected function connect($db_name) {
	    $this->mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, $db_name);
	    if ($this->mysqli->connect_errno != 0) {
	      	if ($this->mysqli->connect_errno == 2002) 
	      		throw new Exception("ERROR_HOST"); // Выбрасываем исключение, если ошибка связана с адресом хоста
	      	elseif ($this->mysqli->connect_errno == 1044) 
	      		throw new Exception("ERROR_AUTH"); // Выбрасываем исключение, если ошибка связана с именем пользователя и паролем
	      	elseif ($this->mysqli->connect_errno == 1049) 
	      		throw new Exception("ERROR_DB"); // Выбрасываем исключение, если ошибка связана с названием базы данных
		}
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

	protected function _delete($table, $where = [], $order = null, $limit = null) {
		$query = "DELETE FROM `{$table}`";

		if (!empty($where)) {
			$query .= " WHERE";
			foreach ($where as $value) {
				$query .= " {$value[0]} ";
				if(count($value) > 1) {
					$query .= "( `{$value[1]}` {$value[2]} {$value[3]} )";
				}	
			}
		}

		if (!empty($order)) {
			$query .= " ORDER BY";
			$temp = [];
			foreach ($order as $value) {
				$temp[] = " `{$value[0]}` {$value[1]}";
			}
			$query .= implode(",", $temp);
		}

		if (!empty($limit)) {
			$query .= " LIMIT {$limit[0]}";
			if(!empty($limit[1]))
				$query .= " OFFSET {$limit[1]}";
		}

		return $query;
	}

	protected function _insert($table, $data) {	
		$keys = '';
		$values = '';

		foreach($data as $key => $val) {

			$keys .= '`' . $this->mysqli->real_escape_string($key) . '`, ';

			if(gettype($val) === 'integer') 
				$values .= $this->mysqli->real_escape_string($val) . ' , ';
			else 
				$values .= '\''.$this->mysqli->real_escape_string($val).'\', ';
		}

		$keys = substr($keys, 0, -2);
		$values = substr($values, 0, -2);
		
		$query = "INSERT INTO `$table` ($keys) VALUES ($values)";

		return $query;
	}

	protected function _where($type, $field, $znak, $val) {
		if(in_array($znak, $this->znak_s))
		{
			if($val == null) {
				$val = $znak;
				$znak = "=";
			}
			if(in_array($val, $this->vubor)) {
				$type = $val;
				$val = $znak;
				$znak = "=";
			}


			if(!is_integer($val) && $val[0] != ":" && $val[0] != "?")
				$val = "'" . $val . "'";
			return [$type, str_replace(".", "`.`", $field), $znak, $val];
		}
		else {
			if($val == null) {
				$val = $znak;
				
			}
			$znak = "=";
			if(in_array($val, $this->vubor)) {
				$type = $val;
				$val = $znak;
			}
			if(!is_integer($val) && $val[0] != ":" && $val[0] != "?")
				$val = "'" . $val . "'";
			return [$type, str_replace(".", "`.`", $field), $znak, $val];
		} 	
	}

	public function _set($inp_data) {
		$query;
		foreach($inp_data as $key => $val) {
			if(gettype($val) === 'integer') 
				$query .= '`' . $this->mysqli->real_escape_string($key) . '`=' . $this->mysqli->real_escape_string($val) . ', ';
			else 
				$query .= '`' . $this->mysqli->real_escape_string($key) . '`=\'' . $this->mysqli->real_escape_string($val) . '\', ';
		}
		$this->up_query = substr($query, 0, -2);   
	}

	public function _update($table, $data, $where = []) {

		$query = "UPDATE `{$table}` SET {$data}";
		
		if (!empty($where)) {
			$query .= " WHERE";
			foreach ($where as $value) {
				$query .= " {$value[0]} ";
				if(count($value) > 1) {
					$query .= "( `{$value[1]}` {$value[2]} {$value[3]} )";
				}	
			}
		}

		return $query;
	}
}