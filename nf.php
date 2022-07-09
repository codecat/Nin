<?php

// Runtime paths to framework and content
$nf_dir = __DIR__;
$nf_www_dir = $_SERVER['DOCUMENT_ROOT'];
$nf_uri = '';
$nf_uri_original = '';
$nf_uri_fallback = false;

// Initialize the autoloader early
include('functions/autoloader.php');
spl_autoload_register('nf_autoload');

// Include dependencies
include('functions/define.php');
include('functions/config.php');
include('functions/begin.php');
include('functions/error.php');
include('functions/routing.php');
include('functions/hook.php');
include('functions/i18n.php');
include('functions/db.php');
include('functions/cache.php');

function nf_begin($options = [], $options_deprecated = [])
{
	// Small compatibility fix for every older Nin site
	if (is_string($options)) {
		$options = $options_deprecated;
	}
	nf_begin_internal($options);
}
