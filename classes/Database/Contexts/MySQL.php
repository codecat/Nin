<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;

class MySQL extends Context
{
	private $connection = null;

	public function __construct($options)
	{
		$options = array_merge([
			'hostname' => 'localhost',
			'username' => '',
			'password' => '',
			'database' => '',
			'encoding' => 'utf8mb4'
		], $options);

		if(!class_exists('mysqli')) {
			nf_error(14, 'MySQLi');
			exit;
		}

		$this->connection = new \mysqli(
			$options['hostname'],
			$options['username'],
			$options['password'],
			$options['database']
		);
		$this->connection->set_charset($options['encoding']);
		$this->connection->query('SET NAMES ' . $options['encoding']);

		if($this->connection->connect_errno != 0) {
			nf_error(7);
		}
	}

	public function real_escape_string($str)
	{
		return $this->connection->real_escape_string($str);
	}

	public function query($query)
	{
		$ret = $this->connection->query($query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . $this->connection->error . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\MySQL($ret, $this->connection->insert_id);
	}

	public function beginBuild($table)
	{
		return new \Nin\Database\QueryBuilders\MySQL($this, $table);
	}
}
