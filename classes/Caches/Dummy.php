<?php

namespace Nin\Caches;

use Nin\Cache;

class Dummy extends Cache
{
	private $memory = [];

	public function __construct($options)
	{
	}

	public function set($key, $value, $ttl = 0)
	{
		$this->memory[$key] = [$value, time() + $ttl];
	}

	public function get($key)
	{
		if(!isset($this->memory[$key])) {
			return null;
		}
		$obj = $this->memory[$key];
		if($obj[1] > time()) {
			return null;
		}
		return $obj[0];
	}

	public function clear()
	{
		$this->memory = [];
	}

	public function delete($key)
	{
		unset($this->memory[$key]);
	}
}
