<?php

namespace Nin\Database;

abstract class QueryBuilder
{
	protected $context;
	protected $table = '';
	protected $method;
	protected $where;
	protected $group;
	protected $set;
	protected $insertValues;

	public function __construct($context, $table)
	{
		$this->context = $context;
		$this->table = $table;
		$this->clear();
	}

	public function clear()
	{
		$this->method = '';
		$this->where = array();
		$this->set = array();
	}

	private function setMethod($method)
	{
		$this->clear();
		$this->method = $method;
	}

	public function select() { $this->setMethod('SELECT'); return $this; }
	public function update() { $this->setMethod('UPDATE'); return $this; }
	public function insert() { $this->setMethod('INSERT'); return $this; }
	public function delete() { $this->setMethod('DELETE'); return $this; }
	public function count() { $this->setMethod('COUNT'); return $this; }
	public function findpk() { $this->setMethod('FINDPK'); return $this; }

	public function whereAssoc($arr)
	{
		foreach($arr as $k => $v) {
			$this->where($k, $v);
		}
		return $this;
	}
	public function where($key, $value = null, $oper = '=')
	{
		if(is_array($key)) {
			return $this->whereAssoc($key);
		}
		$this->where[] = array(
			$key, $value, $oper
		);
		return $this;
	}

	public function group($key)
	{
		$this->group = $key;
		return $this;
	}

	public function setAssoc($arr)
	{
		foreach($arr as $k => $v) {
			$this->set($k, $v);
		}
		return $this;
	}
	public function set($key, $value = null)
	{
		if(is_array($key)) {
			return $this->setAssoc($key);
		}
		$this->set[] = array(
			$key, $value
		);
		return $this;
	}

	public function values($arr)
	{
		$this->insertValues[] = $arr;
		return $this;
	}

	public function execute()
	{
		return $this->context->query($this->build());
	}

	public function executeCount()
	{
		$res = $this->context->query($this->build());
		if($res === false) {
			return 0;
		}
		$row = $res->fetch_assoc();
		return intval($row['c']);
	}

	public abstract function build();
}
