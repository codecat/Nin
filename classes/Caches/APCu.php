<?php

namespace Nin\Caches;

use Nin\Cache;

class APCu extends Cache
{
	private $prefix = 'nin_';

	public function __construct($options)
	{
		$options = array_merge(array(
			'prefix' => 'nin_'
		), $options);

		if(!function_exists('apcu_store')) {
			nf_error(14, 'APCu');
			exit;
		}

		if (array_key_exists('prefix', $options)) {
			$this->prefix = $options['prefix'];
		}
	}

	public function set($key, $value, $ttl = 0)
	{
		apcu_store($this->prefix . $key, $value, $ttl);
	}

	public function get($key)
	{
		$ok = false;
		$ret = apcu_fetch($this->prefix . $key, $ok);
		if(!$ok) {
			return null;
		}
		return $ret;
	}

	public function clear()
	{
		apcu_clear_cache();
	}

	public function delete($key)
	{
		apcu_delete($this->prefix . $key);
	}
}
