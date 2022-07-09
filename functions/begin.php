<?php

/*
 * Begin the framework.
 */
function nf_begin_internal($options)
{
	global $nf_uri;
	global $nf_uri_original;
	global $nf_cfg;

	nf_config_initialize($options);

	if ($nf_cfg['user']['sessions'] && session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if($nf_cfg['debug']['enabled']) {
		register_shutdown_function('nf_php_fatal');
		set_error_handler('nf_php_error');
		set_exception_handler('nf_php_exception');
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 'off');
		error_reporting(0);
	}

	nf_i18n_initialize();
	nf_db_initialize();
	nf_cache_initialize();

	$nf_uri = $_SERVER['REQUEST_URI'];
	$uri_part = strstr($nf_uri, '?', true);
	if($uri_part) {
		$nf_uri = $uri_part;
	}
	$nf_uri_original = $nf_uri;

	$rethookuri = nf_hook_one('uri', [$nf_uri]);
	if($rethookuri !== null) {
		$nf_uri = $rethookuri;
	}

	nf_handle_uri($nf_uri);
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
