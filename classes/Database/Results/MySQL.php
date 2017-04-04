<?php

namespace Nin\Database\Results;

use Nin\Database\Result;

class MySQL extends Result
{
	private $res;
	private $insert_id;

	public function __construct($res, $insert_id = false)
	{
		$this->res = $res;
		$this->insert_id = $insert_id;
	}

	public function fetch_assoc()
	{
		return $this->res->fetch_assoc();
	}

	public function insert_id()
	{
		return $this->insert_id;
	}
}
