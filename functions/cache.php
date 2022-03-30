<?php

/**
 * The global cache object
 * @var Nin\Cache|false
 */
$nf_cache = false;

/**
 * Initializes the cache object.
 */
function nf_cache_initialize()
{
	global $nf_cfg;
	global $nf_cache;

	if(isset($nf_cfg['apc'])) {
		$nf_cfg['cache']['class'] = 'APC';
		$nf_cfg['cache']['options'] = $nf_cfg['apc'];
	} elseif(isset($nf_cfg['redis'])) {
		$nf_cfg['cache']['class'] = 'PhpRedis';
		$nf_cfg['cache']['options'] = $nf_cfg['redis'];
	}

	$class_name = $nf_cfg['cache']['class'];
	$class_name_internal = 'Nin\\Caches\\' . $class_name;

	$options = $nf_cfg['cache']['options'];

	if(class_exists($class_name)) {
		$nf_cache = new $class_name($options);
	} elseif(class_exists($class_name_internal)) {
		$nf_cache = new $class_name_internal($options);
	} else {
		nf_error(13, $class_name . ', ' . $class_name_internal);
		return;
	}
}

/**
 * Get the current cache object.
 */
function nf_cache()
{
	global $nf_cache;
	return $nf_cache;
}
