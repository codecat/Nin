<?php

namespace Nin\Database\Results;

use Nin\Database\Result;

class SQLite extends Result
{
	private $res;
	private $insert_id;
	private $row_count;

	public function __construct($res, $insert_id = false)
	{
		$this->res = $res;
		$this->insert_id = $insert_id;

		// We have to get the number of rows in the result early!
		$this->row_count = 0;
		if ($this->res->numColumns() > 0) {
			while ($this->res->fetchArray(SQLITE3_NUM) !== false) {
				$this->row_count++;
			}
			$this->res->reset();
		}
	}

	public function fetch_assoc()
	{
		return $this->res->fetchArray(SQLITE3_ASSOC);
	}

	public function insert_id($key = 'ID')
	{
		//TODO: Can we do better?
		return $this->insert_id;
	}

	public function num_rows()
	{
		return $this->row_count;
	}
}
