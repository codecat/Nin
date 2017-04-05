<?php

namespace Nin\Caches;

use Nin\Cache;

class APC extends Cache
{
	private $prefix = 'nin_';

	public function __construct($options)
	{
		$options = array_merge(array(
			'prefix' => 'nin_'
		), $options);
	}

	public function set($key, $value, $ttl = 0)
	{
		apc_store($this->$prefix . $key, $value, $ttl);
	}

	public function get($key)
	{
		$ok = false;
		$ret = apc_fetch($this->$prefix . $key, $ok);
		if(!$ok) {
			return null;
		}
		return $ret;
	}

	public function clear()
	{
		apc_clear_cache('user');
	}

	public function delete($key)
	{
		apc_delete($this->$prefix . $key);
	}

	public function take($key, $ttl, $cb)
	{
		$ok = false;
		$ret = apc_fetch($this->$prefix . $key, $ok);
		if(!$ok) {
			$ret = $cb();
			apc_store($this->$prefix . $key, $ret, $ttl);
		}
		return $ret;
	}
}
