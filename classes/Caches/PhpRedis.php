<?php

namespace Nin\Caches;

use Nin\Cache;

class PhpRedis extends Cache
{
	private $connection = false;

	public function __construct($options)
	{
		$options = array_merge(array(
			'host' => '127.0.0.1',
			'port' => 6379,
			'timeout' => 0.0,
			'retry_interval' => 0,
			'password' => '',
			'database' => 0,
		), $options);

		if(!class_exists('Redis')) {
			nf_error(14, 'phpredis');
			exit;
		}

		$this->connection = new \Redis();
		$this->connection->connect($options['host'], $options['port'], $options['timeout'], NULL, $options['retry_interval']);
		if($options['password'] != '') {
			$this->connection->auth($options['password']);
		}
		$this->connection->select($options['database']);
	}

	public function set($key, $value, $ttl = 0)
	{
		return $this->connection->setEx($key, $ttl, $value);
	}

	public function get($key)
	{
		$ret = $this->connection->get($key);
		if($ret === false) {
			return null;
		}
		return $ret;
	}

	public function clear()
	{
		$this->connection->flushDb();
	}

	public function delete($key)
	{
		$this->connection->delete($key);
	}
}
