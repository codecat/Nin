<?php

use Nin\Nin;

// Internationalization cache
$nf_i18n = [];

/**
 * Loads the language files for the currently active language.
 */
function nf_i18n_initialize()
{
	global $nf_www_dir;
	global $nf_i18n;
	global $nf_cfg;

	$lang = Nin::language();

	$nf_i18n = [];
	nf_i18n_loadtable(__DIR__ . '/' . $nf_cfg['paths']['i18n'] . '/' . $lang . '.php');
	nf_i18n_loadtable($nf_www_dir . '/' . $nf_cfg['paths']['i18n'] . '/' . $lang . '.php');
}

/**
 * Load a translation table from the given path.
 */
function nf_i18n_loadtable($path)
{
	global $nf_i18n;

	if(!file_exists($path)) {
		return;
	}

	$table = include($path);
	$nf_i18n = array_merge($nf_i18n, $table);
}

/**
 * Translate a string.
 */
function nf_t($str, $params = [])
{
	global $nf_i18n;

	$ret = $str;
	if (array_key_exists($str, $nf_i18n)) {
		$ret = $nf_i18n[$str];
	}

	foreach($params as $k => $v) {
		$ret = str_replace($k, $v, $ret);
	}

	return $ret;
}
