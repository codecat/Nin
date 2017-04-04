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

	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	nf_init_autoloader();

	nf_init_config($options);

	$nf_www_dir = $dir;

	nf_i18n_init();

	if($nf_cfg['debug']['enabled']) {
		register_shutdown_function('nf_php_fatal');
		set_error_handler('nf_php_error');
		set_exception_handler('nf_php_exception');
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);
	}

	$using_controllers = file_exists($dir . DIRECTORY_SEPARATOR . $nf_cfg['paths']['controllers']);
	$has_htaccess = file_exists($dir . DIRECTORY_SEPARATOR . '.htaccess');
	if(!isset($nf_cfg['no_htaccess']) && $using_controllers && !$has_htaccess) {
		echo '<b>' . nf_t('Warning:') . '</b> ' . nf_t('.htaccess does not exist.');
		$ok = copy($nf_dir . DIRECTORY_SEPARATOR . '.htaccess', $dir . DIRECTORY_SEPARATOR . '.htaccess');
		if($ok) {
			echo ' ' . nf_t('Nin was able to create it automatically for you. Refresh for it to take effect.') . '<br>';
		} else {
			echo ' ' . nf_t('Nin was not able to automatically create the file.');
			echo ' ' . nf_t('Please copy it manually from:') . ' <code>' . $nf_dir . DIRECTORY_SEPARATOR . '.htaccess</code><br>';
		}
		echo ' ' . nf_t('To ignore this warning and stop this behavior, set \'no_htaccess\' in the config to true.');
		return;
	}

	if($nf_cfg['db']['enabled']) {
		if(!nf_sql_connect(
			$nf_cfg['db']['hostname'],
			$nf_cfg['db']['username'],
			$nf_cfg['db']['password'],
			$nf_cfg['db']['database'],
			$nf_cfg['db']['encoding'])) {
			nf_error(7);
		}
	}

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
