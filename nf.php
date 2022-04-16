<?php

// Runtime paths to framework and content
$nf_dir = '';
$nf_www_dir = '';
$nf_uri = '';
$nf_uri_original = '';
$nf_uri_fallback = false;

// Include dependencies
include('functions/define.php');
include('functions/config.php');
include('functions/autoloader.php');
include('functions/begin.php');
include('functions/error.php');
include('functions/routing.php');
include('functions/hook.php');
include('functions/i18n.php');
include('functions/db.php');
include('functions/cache.php');

function nf_begin($dir, $options = [])
{
	global $nf_dir;
	$nf_dir = __DIR__;
	nf_begin_internal($dir, $options);
}
