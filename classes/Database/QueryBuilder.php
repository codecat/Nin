<?php

namespace Nin\Database;

abstract class QueryBuilder
{
	protected $context;
	protected $table = '';
	protected $method;
	protected $where;
	protected $group;
	protected $orderby;
	protected $limit;
	protected $get;
	protected $set;
	protected $insertValues;
	protected $insertReturning = "ID";

	public function __construct($context, $table)
	{
		$this->context = $context;
		$this->table = $table;
		$this->clear();
	}

	public function clear()
	{
		$this->method = '';
		$this->where = [];
		$this->group = '';
		$this->orderby = [];
		$this->limit = [-1, -1];
		$this->get = [];
		$this->set = [];
		$this->insertValues = [];
	}

	private function setMethod($method)
	{
		$this->method = $method;
	}

	public function select() { $this->setMethod('SELECT'); return $this; }
	public function update() { $this->setMethod('UPDATE'); return $this; }
	public function insert() { $this->setMethod('INSERT'); return $this; }
	public function delete() { $this->setMethod('DELETE'); return $this; }
	public function count() { $this->setMethod('COUNT'); return $this; }

	public function whereAssoc($arr, $oper = '=', $logical = 'AND')
	{
		foreach($arr as $k => $v) {
			$this->where($k, $v, $oper, $logical);
		}
		return $this;
	}
	public function where($key, $value = null, $oper = '=', $logical = 'AND')
	{
		if(is_array($key)) {
			return $this->whereAssoc($key, $oper, $logical);
		}
		$this->where[] = [
			$key, $value, $oper, $logical
		];
		return $this;
	}

	public function group($key)
	{
		$this->group = $key;
		return $this;
	}

	public function orderby($key, $sort = 'ASC')
	{
		$this->orderby[] = [
			$key, $sort
		];
		return $this;
	}

	public function limit($start, $end = null)
	{
		if($end === null) {
			$this->limit = [0, intval($start)];
		} else {
			$this->limit = [intval($start), intval($end)];
		}
		return $this;
	}

	public function getArray($arr)
	{
		foreach($arr as $key) {
			$this->get[] = $key;
		}
		return $this;
	}
	public function get($key)
	{
		if(is_array($key)) {
			return $this->getArray($key);
		}
		$this->get[] = $key;
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
		$this->set[] = [
			$key, $value
		];
		return $this;
	}

	public function values($arr)
	{
		$this->insertValues[] = $arr;
		return $this;
	}

	public function returning($value = true)
	{
		$this->insertReturning = $value;
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
