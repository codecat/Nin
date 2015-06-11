<?php

class Model
{
	public $_data = array();
	public $_changed = array();
	public $_loaded = false;
	public $_relationalrows = array();
	
	public function relations()
	{
		return array();
	}
	
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
	
	public static function findByAttributes($attributes)
	{
		$class = get_called_class();
		$tablename = static::tablename();
		$query = 'SELECT * FROM `' . $tablename . '` ';
		if(count($attributes) > 0) {
			$query .= 'WHERE ';
			$firstAnd = false;
			foreach($attributes as $k => $v) {
				if($firstAnd) {
					$query .= 'AND ';
				}
				$firstAnd = true;
				$query .= '`' . $k . '`=' . nf_sql_encode($v) . ' ';
			}
		}
		$query .= 'LIMIT 0,1';
		$res = nf_sql_query($query);
		if($res !== false) {
			$row = $res->fetch_assoc();
			if($row) {
				$ret = new $class();
				$ret->loadRow($row);
				return $ret;
			}
		}
		return false;
	}
	
	public static function findAllByAttributes($attributes)
	{
		$class = get_called_class();
		$tablename = static::tablename();
		$query = 'SELECT * FROM `' . $tablename . '` ';
		if(count($attributes) > 0) {
			$query .= 'WHERE ';
			$firstAnd = false;
			foreach($attributes as $k => $v) {
				if($firstAnd) {
					$query .= 'AND ';
				}
				$firstAnd = true;
				$query .= '`' . $k . '`=' . nf_sql_encode($v) . ' ';
			}
		}
		$ret = array();
		$res = nf_sql_query($query);
		if($res !== false) {
			while($row = $res->fetch_assoc()) {
				$obj = new $class();
				$obj->loadRow($row);
				$ret[] = $obj;
			}
		}
		return $ret;
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
	
	public function remove()
	{
		if(!$this->_loaded) {
			return false;
		}
		$tablename = static::tablename();
		$pk_col = static::findPrimaryKey();
		$pk = $this->_data[$pk_col];
		$query = 'DELETE FROM `' . $tablename . '` WHERE `' . $pk_col . '`=' . nf_sql_encode($pk);
		return nf_sql_query($query) !== false;
	}
	
	protected function lookupRelation($k, $v)
	{
		if(isset($this->_relationalrows[$k])) {
			return $this->_relationalrows[$k];
		}
		$obj = false;
		
		$pk_col = static::findPrimaryKey();
		$pk = $this->_data[$pk_col];
		
		if($v[0] == BELONGS_TO) {
			$their_classname = $v[1];
			$my_column = $v[2];
			$obj = $their_classname::findByPk($this->$my_column);
			
		} elseif($v[0] == HAS_MANY) {
			$their_classname = $v[1];
			$their_column = $v[2];
			$obj = $their_classname::findAllByAttributes(array($their_column => $pk));
			
		} elseif($v[0] == HAS_ONE) {
			$their_classname = $v[1];
			$their_column = $v[2];
			$obj = $their_classname::findByAttributes(array($their_column => $pk));
		}
		
		$this->_relationalrows[$k] = $obj;
		return $obj;
	}
	
	public function __get($name)
	{
		// Test relations
		$relations = $this->relations();
		foreach($relations as $k => $v) {
			if($name == $k) {
				return $this->lookupRelation($k, $v);
			}
		}
		
		// Test getters
		$functionname = 'get' . ucfirst($name);
		if(method_exists($this, $functionname)) {
			return $this->$functionname();
		}
		
		// Test columns
		if(isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		
		// Nothing found
		return null;
	}
	
	public function __set($name, $value)
	{
		// Test setters
		$functionname = 'set' . ucfirst($name);
		if(method_exists($this, $functionname)) {
			$this->$functionname($value);
			return;
		}
		
		// Test columns
		$this->_data[$name] = $value;
		if(!in_array($name, $this->_changed)) {
			$this->_changed[] = $name;
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
