<?php

class QueryProvider extends Provider
{
	public $class;
	public $query;
	public $result;

	public function __construct($class, $query, $params = false)
	{
		$q = $query;
		if($params !== false) {
			foreach($params as $k => $v) {
				$q = str_replace($k, nf_sql_encode($v), $q);
			}
		}

		$this->class = $class;
		$this->query = $q;
	}

	public function begin()
	{
		$this->result = nf_sql_query($this->query);
	}

	public function end()
	{
	}

	public function count()
	{
		return $this->result->num_rows;
	}

	public function getNext()
	{
		if(!$this->result) {
			return null;
		}

		$row = $this->result->fetch_assoc();
		if(!$row) {
			return null;
		}

		$ret = new $this->class();
		$ret->loadRow($row);
		return $ret;
	}
}
