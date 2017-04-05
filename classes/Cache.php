<?php

namespace Nin;

abstract class Cache
{
	/**
	 * Set a cache value with the given ttl in seconds.
	 */
	public abstract function set($key, $value, $ttl = 0);

	/**
	 * Get a cache value. If it doesn't exist, it returns null.
	 */
	public abstract function get($key);

	/**
	 * Clear the entire cache.
	 */
	public abstract function clear();

	/**
	 * Delete a specific key in the cache.
	 */
	public abstract function delete($key);

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
	public function take($key, $ttl, $cb)
	{
		$obj = $this->get($key);
		if($obj === null) {
			$obj = $cb();
			$this->set($key, $obj, $ttl);
		}
		return $obj;
	}
}
