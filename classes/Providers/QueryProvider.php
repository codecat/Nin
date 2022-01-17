<?php

namespace Nin\Providers;

use Nin\Provider;

class QueryProvider extends Provider
{
	public $query;
	public $result;

	public function __construct($builder)
	{
		$this->query = $builder->build();
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

		return $this->result->fetch_assoc();
	}
}
