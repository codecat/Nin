<?php

class Cache
{
	public static $skip = false;

	/**
	 * Set a cache value with the given ttl in seconds.
	 */
	public static function set($key, $value, $ttl = 0)
	{
		if(Cache::$skip) {
			return;
		}
		apc_store($key, $value, $ttl);
	}

	/**
	 * Get a cache value. If it doesn't exist, it returns null.
	 */
	public static function get($key)
	{
		if(Cache::$skip) {
			return null;
		}
		$ok = false;
		$ret = apc_fetch($key, $ok);
		if(!$ok) {
			return null;
		}
		return $ret;
	}

	/**
	 * Clear the entire cache.
	 */
	public static function clear()
	{
		if(Cache::$skip) {
			return;
		}
		apc_clear_cache('user');
	}

	/**
	 * Delete a specific key in the cache.
	 */
	public static function delete($key)
	{
		if(Cache::$skip) {
			return;
		}
		apc_delete($key);
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
		if(Cache::$skip) {
			return $cb();
		}
		$ok = false;
		$ret = apc_fetch($key, $ok);
		if(!$ok) {
			$ret = $cb();
			apc_store($key, $ret, $ttl);
		}
		return $ret;
	}
}
