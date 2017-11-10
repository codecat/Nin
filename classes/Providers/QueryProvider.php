<?php

namespace Nin\Providers;

use Nin\Provider;

class QueryProvider extends Provider
{
	public $class;
	public $query;
	public $result;

	public function __construct($class, $query)
	{
		$this->class = $class;
		$this->query = $query;
	}

	public function begin()
	{
		$this->result = nf_db_context()->query($this->query);
	}

	public function end()
	{
	}

	public function count()
	{
		return $this->result->num_rows();
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
