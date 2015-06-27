<?php

class Cache
{
	/**
	 * Set a cache value with the given ttl in seconds.
	 */
	public static function set($key, $value, $ttl = 0)
	{
		apc_store($key, $value, $ttl);
	}

	/**
	 * Get a cache value. If it doesn't exist, it returns null.
	 */
	public static function get($key)
	{
		$ok = false;
		$ret = apc_fetch($key, $ok);
		if(!$ok) {
			return null;
		}
		return $ret;
	}

	/**
	 * Get a cache value. If it doesn't exist, it will set the
	 * value by using the $cb anonymous function. The return value
	 * of that function will be the new cached value. $ttl is used
	 * as the ttl in seconds.
	 *
	 * Either returns the cached value, or the newly cached value.
	 *
	 * Example:
	 *   $foo = 'bar';
	 *   $value = Cache::take('some_value', 3600, function() use ($foo) {
	 *     return $foo . $foo;
	 *   });
	 */
	public static function take($key, $ttl, $cb)
	{
		$ok = false;
		$ret = apc_fetch($key, $ok);
		if(!$ok) {
			$ret = $cb();
			apc_store($key, $ret, $ttl);
		}
		return $ret;
	}
}
