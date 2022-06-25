<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;

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

		if($this->connection->lastErrorCode() != 0) {
			nf_error(7);
		}
	}

	public function real_escape_string($str)
	{
		return $this->connection->escapeString($str);
	}

	public function query($query)
	{
		$ret = $this->connection->query($query);
		if($ret === false) {
			nf_error(10, nf_t('Error was:') . ' ' . $this->connection->lastErrorMsg() . ' - ' . nf_t('Query was:') . ' ' . $query);
		}
		return new \Nin\Database\Results\SQLite($ret, $this->connection->lastInsertRowID());
	}

	public function beginBuild($table)
	{
		return new \Nin\Database\QueryBuilders\SQLite($this, $table);
	}
}
