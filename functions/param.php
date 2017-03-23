<?php

/**
 * Return the parameter from $nf_cfg['params'] with an optional default value if the param does not exist.
 * You can use periods to delimit between arrays.
 *
 * For example, you can access $nf_cfg['params']['test']['foo']['bar'] by using the key:
 *  "test.foo.bar"
 */
function nf_param($key, $default = null)
{
	global $nf_cfg;
	$keys = explode('.', $key);
	$ret = $nf_cfg['params'];
	foreach($keys as $k) {
		if(!isset($ret[$k])) {
			return $default;
		}
		$ret = $ret[$k];
	}
	return $ret;
}
