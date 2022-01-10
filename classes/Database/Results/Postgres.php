<?php

namespace Nin\Database\Results;

use Nin\Database\Result;

class Postgres extends Result
{
	private $res;

	public function __construct($res)
	{
		$this->res = $res;
	}

	public function fetch_assoc()
	{
		return pg_fetch_assoc($this->res);
	}

	public function insert_id()
	{
		//TODO:
		// INSERT INTO ... RETURNING "ID"
		$row = pg_fetch_assoc($this->res);
		return $row['ID'];
	}

	public function num_rows()
	{
		return pg_num_rows($this->res);
	}
}
