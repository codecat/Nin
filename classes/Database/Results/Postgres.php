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
		$ret = pg_fetch_assoc($this->res);

		if ($ret) {
			$numfields = pg_num_fields($this->res);
			for ($i = 0; $i < $numfields; $i++) {
				$fieldname = pg_field_name($this->res, $i);
				$fieldtype = pg_field_type($this->res, $i);

				switch ($fieldtype) {
					case 'int2':
					case 'int4':
					case 'int8':
						$ret[$fieldname] = intval($ret[$fieldname]);
						break;

					case 'bool':
						$ret[$fieldname] = ($ret[$fieldname] == 't');
						break;
				}
			}
		}

		return $ret;
	}

	public function insert_id()
	{
		$row = pg_fetch_assoc($this->res);
		return $row['ID'];
	}

	public function num_rows()
	{
		return pg_num_rows($this->res);
	}
}
