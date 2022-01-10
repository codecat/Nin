<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;

class Postgres extends Context
{
	private $connection = null;

	public function __construct($options)
	{
		$options = array_merge(array(
			'hostname' => 'localhost',
			'username' => 'postgres',
			'password' => '',
			'database' => 'postgres',
		), $options);

		$this->connection = pg_connect(
			'host=' . $options['hostname'] .
			' user=' . $options['username'] .
			' password=' . $options['password'] .
			' dbname=' . $options['database']
		);

		if(!$this->connection) {
			nf_error(7);
		}
	}

	public function real_escape_string($str)
	{
		return pg_escape_string($this->connection, $str);
	}

	public function query($query)
	{
		$ret = pg_query($this->connection, $query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . pg_last_error($this->connection) . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\Postgres($ret, $this->connection);
	}

	public function beginBuild($table)
	{
		return new \Nin\Database\QueryBuilders\Postgres($this, $table);
	}
}
