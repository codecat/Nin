<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;
use Nin\Database\SchemaColumn;

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

	public function query(string $query)
	{
		$ret = $this->connection->query($query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . $this->connection->error . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\MySQL($ret, $this->connection->insert_id);
	}

	public function beginBuild(string $table)
	{
		return new \Nin\Database\QueryBuilders\MySQL($this, $table);
	}

	public function getSchema(string $table)
	{
		$res = $this->query('SHOW COLUMNS FROM ' . $table . ';');
		/*
			+---------+--------------+------+-----+---------+----------------+
			| Field   | Type         | Null | Key | Default | Extra          |
			+---------+--------------+------+-----+---------+----------------+
			| id      | int(11)      | NO   | PRI | <null>  | auto_increment |
			| author  | varchar(255) | YES  |     | <null>  |                |
			| message | mediumtext   | YES  |     | <null>  |                |
			| time    | int(11)      | YES  |     | <null>  |                |
			+---------+--------------+------+-----+---------+----------------+
		*/
		$ret = [];
		while ($row = $res->fetch_assoc()) {
			$c = new SchemaColumn();
			$c->name = $row['Field'];
			$c->type = strtolower($row['Type']);
			$ret[$c->name] = $c;
		}
		return $ret;
	}
}
