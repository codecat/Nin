<?php

class Model
{
	public $_data = array();
	public $_changed = array();
	public $_loaded = false;
	
	public static function findPrimaryKey()
	{
		$tablename = static::tablename();
		$res = nf_sql_query('SHOW KEYS FROM `' . $tablename . '` WHERE Key_name = \'PRIMARY\'');
		$row = $res->fetch_assoc();
		if($row !== null) {
			return $row['Column_name'];
		}
		nf_error(9, $tablename);
		return false;
	}
	
	public static function findByPk($pk)
	{
		$class = get_called_class();
		$tablename = static::tablename();
		$res = nf_sql_query('SELECT * FROM `' . $tablename . '` WHERE `' . static::findPrimaryKey() . '`=' . nf_sql_encode($pk));
		if($res !== false) {
			$ret = new $class();
			$ret->loadRow($res->fetch_assoc());
			return $ret;
		}
		return false;
	}
	
	public static function findAll()
	{
		$class = get_called_class();
		$tablename = static::tablename();
		$res = nf_sql_query('SELECT * FROM `' . $tablename . '`');
		$ret = array();
		while($row = $res->fetch_assoc()) {
			$obj = new $class();
			$obj->loadRow($row);
			$ret[] = $obj;
		}
		return $ret;
	}
	
	public function insert()
	{
		$tablename = static::tablename();
		$pk_col = static::findPrimaryKey();
		$query = 'INSERT INTO `' . $tablename . '` (';
		$values = '';
		foreach($this->_data as $k => $v) {
			$query .= '`' . $k . '`,';
			$values .= nf_sql_encode($v) . ',';
		}
		$query = rtrim($query, ',');
		$query .= ') VALUES(' . rtrim($values, ',') . ')';
		$res = nf_sql_query($query);
		if($res !== false) {
			$this->_loaded = true;
			return true;
		}
		return false;
	}
	
	public function save()
	{
		if(!$this->_loaded) {
			return $this->insert();
		}
		$tablename = static::tablename();
		$query = 'UPDATE `' . $tablename . '` SET ';
		foreach($this->_changed as $k) {
			$query .= '`' . $k . '`=' . nf_sql_encode($this->_data[$k]) . ',';
		}
		$query = rtrim($query, ',');
		$pk_col = static::findPrimaryKey();
		$pk = $this->_data[$pk_col];
		$query .= ' WHERE `' . $pk_col . '`=' . nf_sql_encode($pk);
		return nf_sql_query($query) !== false;
	}
	
	public function __get($name)
	{
		if(isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		return null;
	}
	
	public function __set($name, $value)
	{
		$functionname = 'set' . ucfirst($name);
		if(method_exists($this, $functionname)) {
			$this->$functionname($value);
			return;
		}
		
		$found = false;
		foreach($this->_data as $k => $v) {
			if($k === $name) {
				if($v != $this->_data[$k]) {
					$this->_data[$k] = $v;
					if(!in_array($k, $this->_changed)) {
						$this->_changed[] = $k;
					}
				}
				$found = true;
				break;
			}
		}
		
		if(!$found) {
			$this->_data[$name] = $value;
		}
	}
	
	public function _isset($name)
	{
		return isset($this->_data[$name]);
	}
	
	public function loadRow($row)
	{
		$this->_data = $row;
		$this->_loaded = true;
	}
	
	public function setParameters($params)
	{
		$this->_data = $params;
	}
}
