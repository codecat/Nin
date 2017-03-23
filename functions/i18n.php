<?php

// Internationalization cache
$nf_i18n = array();

/**
 * Loads the language files for the currently active language.
 */
function nf_i18n_init()
{
	global $nf_www_dir;
	global $nf_i18n;
	global $nf_cfg;

	$lang = Nin::language();

	$nf_i18n = array();
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
function nf_t($str, $params = array())
{
	global $nf_i18n;

	$ret = @$nf_i18n[$str];
	if($ret !== null) {
		foreach($params as $k => $v) {
			$ret = str_replace($k, $v, $ret);
		}
		return $ret;
	}

	return $str;
}
