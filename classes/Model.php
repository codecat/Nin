<?php

namespace Nin;

class Model
{
	public $_data = array();
	public $_changed = array();
	public $_loaded = false;
	public $_relationalrows = array();
	public $_errors = array();

	public function relations()
	{
		return array();
	}

	public function rules()
	{
		return array();
	}

	public static function columns()
	{
		return array();
	}

	public static function primarykey()
	{
		return 'ID';
	}

	public static function findByPk($pk)
	{
		$class = get_called_class();
		return $class::findByResult(
			nf_db_beginbuild(static::tablename())
				->select()
				->get(static::columns())
				->where(static::primarykey(), $pk)
				->execute()
		);
	}

	public static function findByAttributes($attributes)
	{
		$class = get_called_class();
		return $class::findByResult(
			nf_db_beginbuild(static::tablename())
				->select()
				->get(static::columns())
				->where($attributes)
				->execute()
		);
	}

	public static function findAllByAttributes($attributes, $options = array())
	{
		$class = get_called_class();
		return $class::findAllByResult(
			static::queryOptions($options,
				nf_db_beginbuild(static::tablename())
					->select()
					->get(static::columns())
					->where($attributes)
			)->execute()
		);
	}

	public static function countByAttributes($attributes)
	{
		return nf_db_beginbuild(static::tablename())
			->count()
			->where($attributes)
			->executeCount();
	}

	public static function findAll($options = array())
	{
		$class = get_called_class();
		return $class::findAllByResult(
			static::queryOptions($options,
				nf_db_beginbuild(static::tablename())
					->select()
					->get(static::columns())
			)->execute()
		);
	}

	public static function beginQuery()
	{
		return new \Nin\Database\ModelQueryBuilder(static::class);
	}

	public static function queryOptions($options, $builder)
	{
		// By default, we always want to sort by primary key ascending
		// Doing this will make sure PostgreSQL's behavior is the same as MySQL's behavior
		$order = 'asc';
		$orderby = static::primarykey();

		if(count($options) > 0) {
			if(isset($options['group'])) {
				$builder->group($options['group']);
			}

			if(isset($options['order'])) {
				$order = $options['order'];
			}
			if(isset($options['orderby'])) {
				$orderby = $options['orderby'];
			}

			if(isset($options['limit'])) {
				$l = $options['limit'];
				if(is_int($l)) {
					$builder->limit($l);
				} elseif(is_array($l)) {
					$builder->limit(intval($l[0]), intval($l[1]));
				}
			}
		}

		$builder->orderby($orderby, $order);

		return $builder;
	}

	public static function findByResult($res)
	{
		if($res === false) {
			return false;
		}
		$row = $res->fetch_assoc();
		if(!$row) {
			return false;
		}
		$class = get_called_class();
		$ret = new $class();
		$ret->loadRow($row);
		return $ret;
	}

	public static function findAllByResult($res)
	{
		if($res === false) {
			return array();
		}
		$class = get_called_class();
		$ret = array();
		while($row = $res->fetch_assoc()) {
			$obj = new $class();
			$obj->loadRow($row);
			$ret[] = $obj;
		}
		return $ret;
	}

	public function fetch($column)
	{
		$pk_col = static::primarykey();
		$res = nf_db_beginbuild(static::tablename())
			->select()
			->get($column)
			->where($pk_col, $this->_data[$pk_col])
			->execute();

		if($res->num_rows() != 1) {
			return false;
		}

		$row = $res->fetch_assoc();
		if(is_array($column)) {
			foreach($column as $col) {
				$this->_data[$col] = $row[$col];
			}
			return true;
		} else {
			$this->_data[$column] = $row[$column];
			return $this->_data[$column];
		}
	}

	public function beforeInsert()
	{
		$this->beforeSave();
	}

	public function afterInsert()
	{
		$this->afterSave();
	}

	public function insert()
	{
		$this->beforeInsert();
		$res = nf_db_beginbuild(static::tablename())
			->insert()
			->values($this->_data)
			->execute();
		if($res !== false) {
			$this->_data[static::primarykey()] = $res->insert_id();
			$this->_loaded = true;
			$this->afterInsert();
			return true;
		}
		return false;
	}

	public function beforeSave()
	{
	}

	public function afterSave()
	{
	}

	public function save()
	{
		if(!$this->_loaded) {
			return $this->insert();
		}
		if(count($this->_changed) == 0) {
			return true;
		}
		$this->beforeSave();
		$changed = array();
		foreach($this->_changed as $k) {
			$changed[$k] = $this->_data[$k];
		}
		$pk_col = static::primarykey();
		$res = nf_db_beginbuild(static::tablename())
			->update()
			->where($pk_col, $this->_data[$pk_col])
			->set($changed)
			->execute();
		if($res !== false) {
			$this->_changed = array();
			$this->afterSave();
			return true;
		}
		return false;
	}

	public function remove()
	{
		if(!$this->_loaded) {
			return false;
		}
		$pk_col = static::primarykey();
		$res = nf_db_beginbuild(static::tablename())
			->delete()
			->where($pk_col, $this->_data[$pk_col])
			->execute();
		if($res !== false) {
			$this->_loaded = false;
			return true;
		}
		return false;
	}

	protected function lookupRelation($k, $v)
	{
		if(isset($this->_relationalrows[$k])) {
			return $this->_relationalrows[$k];
		}
		$obj = false;

		if($v[0] == BELONGS_TO) {
			if(!$this->_loaded) {
				return false;
			}

			$their_classname = $v[1];
			$my_column = $v[2];
			$obj = $their_classname::findByPk($this->$my_column);

		} elseif($v[0] == HAS_MANY) {
			if (!$this->_loaded) {
				return array();
			}

			$pk_col = static::primarykey();
			$pk = $this->_data[$pk_col];

			$their_classname = $v[1];
			$their_column = $v[2];
			$options = array();
			if(count($v) >= 4) {
				$options = $v[3];
			}
			$obj = $their_classname::findAllByAttributes(array($their_column => $pk), $options);

		} elseif($v[0] == HAS_ONE) {
			if (!$this->_loaded) {
				return false;
			}

			$pk_col = static::primarykey();
			$pk = $this->_data[$pk_col];

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
		if(!isset($this->_data[$name]) || $this->_data[$name] !== $value) {
			$this->_data[$name] = $value;
			if(!in_array($name, $this->_changed)) {
				$this->_changed[] = $name;
			}
		}
	}

	public function __isset($name)
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
		global $nf_cfg;

		$rules = $this->rules();

		if($nf_cfg['validation']['parameters_exclusive']) {
			// go through each rule
			foreach($rules as $rule) {
				$keys = explode(',', $rule[0]);
				$rulekeys = array_keys($rule);
				if($rule[$rulekeys[1]] === 'unsafe') {
					continue;
				}
				// go through each key of the rule and apply parameters for that key
				foreach($keys as $key) {
					$key = trim($key);
					if(isset($params[$key])) {
						$this->$key = $params[$key];
					}
				}
			}
		} else {
			foreach($params as $k => $v) {
				$unsafe = false;
				// find unsafe rules and check if the key is found
				foreach($rules as $rule) {
					$keys = explode(',', $rule[0]);
					$rulekeys = array_keys($rule);
					if($rule[$rulekeys[1]] !== 'unsafe') {
						continue;
					}
					if(array_search($k, $keys) === false) {
						continue;
					}
					$unsafe = true;
					break;
				}
				// apply if found to be "safe"
				if(!$unsafe) {
					$this->$k = $v;
				}
			}
		}
	}

	public function setAttributes($params)
	{
		$this->setParameters($params);
	}

	public function validate()
	{
		$rules = $this->rules();
		$allok = true;
		$this->_errors = array();
		foreach($rules as $rule) {
			$keys = explode(',', $rule[0]);
			for($i = 0; $i < count($keys); $i++) {
				$keys[$i] = trim($keys[$i]);
			}
			$rulekeys = array_keys($rule);
			$validatorname = 'null';
			$validatorvalue = null;
			if(is_int($rulekeys[1])) {
				$validatorname = $rule[1];
			} else {
				$validatorname = $rulekeys[1];
				$validatorvalue = $rule[$rulekeys[1]];
			}
			$validatorclassname = '\\Nin\\Validators\\' . ucfirst($validatorname) . 'Validator';
			$validator = new $validatorclassname();
			$validator->model = $this;
			$validator->keys = $keys;
			$validator->value = $validatorvalue;
			$validator->arguments = array_slice($rule, 2);
			if(!$validator->validate()) {
				$allok = false;
				$this->_errors[] = ucfirst($validator->error);
			}
		}
		return $allok;
	}
}
