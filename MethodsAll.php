<?php

require_once "Config.php";

abstract class MethodsAll {
	
	protected $dbh; 
	//все таблицы
	protected $tables = ["users", "users1"];
	//текущая таблица
	protected $_t = [];

	protected $where = [];
	protected $order = [];
	protected $join = [];
	protected $groupBy = [];
	protected $having = [];
	protected $limit = null;

	protected function connect() {
		try {
			$this->dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
		} catch (PDOException $e) {
			echo "Ошибка!: " . $e->getMessage() . "<br/>";
		}
	}

	abstract protected function _select($table, $where = [], $join = null, $order = null, 
		$fields = "*", $group_by = null, $having = null, $limit = null);

	abstract protected function _join($table, $field, $linkTo = null, $as = null,  $type = "INNER");

	abstract protected function _where($type, $field, $znak, $val);


}