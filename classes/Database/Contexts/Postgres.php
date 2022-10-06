<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;
use Nin\Database\SchemaColumn;

class Postgres extends Context
{
	private $connection = null;

	public function __construct($options)
	{
		$options = array_merge([
			'hostname' => 'localhost',
			'username' => 'postgres',
			'password' => '',
			'database' => 'postgres',
		], $options);

		$this->connection = pg_connect(
			'host=' . $options['hostname'] .
			' user=' . $options['username'] .
			' password=' . $options['password'] .
			' dbname=' . $options['database']
		);

		if(!$this->connection) {
			nf_error(7);
			return;
		}

		pg_query($this->connection, 'SET TIME ZONE INTERVAL \'' . pg_escape_string($this->connection, date('P')) . '\' HOUR TO MINUTE');
	}

	public function real_escape_string($str)
	{
		return pg_escape_string($this->connection, $str);
	}

	public function query(string $query)
	{
		$ret = pg_query($this->connection, $query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . pg_last_error($this->connection) . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\Postgres($ret, $this->connection);
	}

	public function beginBuild(string $table)
	{
		return new \Nin\Database\QueryBuilders\Postgres($this, $table);
	}

	public function getSchema(string $table)
	{
		$res = $this->query('SELECT * FROM information_schema.columns WHERE table_name = \'' . $table . '\';');
		if (!$res) {
			return false;
		}
		/*
			+-------------+-------------------+
			| column_name | data_type         |
			|-------------+-------------------|
			| id          | integer           |
			| time        | bigint            |
			| author_id   | integer           |
			| message     | character varying |
			+-------------+-------------------+
		*/
		$ret = [];
		while ($row = $res->fetch_assoc()) {
			$c = new SchemaColumn();
			$c->name = $row['column_name'];
			$c->type = strtolower($row['data_type']);
			$ret[$c->name] = $c;
		}
		return $ret;
	}
}
