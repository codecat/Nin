<?php

/*
 * Begin the framework.
 */
function nf_begin_internal($dir, $options)
{
	global $nf_www_dir;
	global $nf_uri;
	global $nf_cfg;
	global $nf_dir;
	global $nf_using_controllers;

	$nf_www_dir = $dir;

	if($nf_cfg['debug']['enabled']) {
		register_shutdown_function('nf_php_fatal');
		set_error_handler('nf_php_error');
		set_exception_handler('nf_php_exception');
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);
	}

	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	nf_init_config($options);

	//TODO: Consistent naming for these
	nf_init_autoloader();
	nf_i18n_init();
	nf_db_initialize();
	nf_cache_initialize();

	$nf_uri = $_SERVER['REQUEST_URI'];
	$uri_part = strstr($nf_uri, '?', true);
	if($uri_part) {
		$nf_uri = $uri_part;
	}

	$rethookuri = nf_hook('uri', array($nf_uri));
	if($rethookuri !== null) {
		$nf_uri = $rethookuri;
	}

	nf_handle_uri($nf_uri);
}

/**
 * Called by nf_begin() to merge the given options with $nf_cfg.
 */
function nf_init_config($options)
{
	global $nf_cfg;
	foreach($options as $k => $v) {
		if(!isset($nf_cfg[$k])) {
			$nf_cfg[$k] = $v;
			continue;
		}
		$nf_cfg[$k] = array_merge($nf_cfg[$k], $v);
	}
}

/**
 * Helper function for manually calling controller routing.
 * This is helpful when writing inline controllers in a single php file.
 */
function nf_begin_routing()
{
	global $nf_uri;
	nf_handle_uri($nf_uri);
}
