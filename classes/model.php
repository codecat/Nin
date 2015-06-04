<?php

class Model
{
	public $_data = array();
	
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
		if(is_string($pk)) {
			$pk = '\'' . nf_sql_escape($pk) . '\'';
		} elseif(is_numeric($pk)) {
			$pk = intval($pk);
		}
		$res = nf_sql_query('SELECT * FROM `' . $tablename . '` WHERE `' . static::findPrimaryKey() . '`=' . $pk);
		if($res !== false) {
			$ret = new $class();
			$ret->parameters = $res->fetch_assoc();
			return $ret;
		}
		return false;
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
		}
		
		foreach($this->_data as $k => $v) {
			if($k === $name) {
				$this->_data[$k] = $v;
				break;
			}
		}
	}
	
	public function _isset($name)
	{
		return isset($this->_data[$name]);
	}
	
	public function setParameters($params)
	{
		$this->_data = $params;
	}
}
