<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;
use Nin\Database\SchemaColumn;

class SQLite extends Context
{
	private $connection = null;

	public function __construct($options)
	{
		$options = array_merge([
			'path' => '',
		], $options);

		if(!class_exists('SQLite3')) {
			nf_error(14, 'SQLite3');
			exit;
		}

		$this->connection = new \SQLite3($options['path']);
		$this->connection->busyTimeout(1000);

		if($this->connection->lastErrorCode() != 0) {
			nf_error(7);
		}
	}

	public function real_escape_string($str)
	{
		return $this->connection->escapeString($str);
	}

	public function query(string $query)
	{
		$ret = $this->connection->query($query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . $this->connection->lastErrorMsg() . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\SQLite($ret, $this->connection->lastInsertRowID());
	}

	public function beginBuild(string $table)
	{
		return new \Nin\Database\QueryBuilders\SQLite($this, $table);
	}

	public function getSchema(string $table)
	{
		$res = $this->query('SELECT * FROM PRAGMA_TABLE_INFO("' . $table . '");');
		if (!$res) {
			return false;
		}
		/*
			+-----+-----------+---------+---------+------------+----+
			| cid | name      | type    | notnull | dflt_value | pk |
			+-----+-----------+---------+---------+------------+----+
			| 0   | id        | INTEGER | 0       | <null>     | 1  |
			| 1   | message   | TEXT    | 0       | <null>     | 0  |
			| 2   | time      | INTEGER | 0       | <null>     | 0  |
			| 3   | author_id | INTEGER | 0       | <null>     | 0  |
			+-----+-----------+---------+---------+------------+----+
		*/
		$ret = [];
		while ($row = $res->fetch_assoc()) {
			$c = new SchemaColumn();
			$c->name = $row['name'];
			$c->type = strtolower($row['type']);
			if ($c->type == 'int') {
				$c->type = 'integer';
			}
			$ret[$c->name] = $c;
		}
		return $ret;
	}
}
