<?php
/**
* Return the URI for the defined assets path
* File can also be passed to get full URI to it
*/
function nf_get_asset_uri($file = null) {
	global $nf_cfg;
	global $nf_www_dir;

	$uri = '//' . $_SERVER["SERVER_NAME"] . '/' . $nf_cfg['paths']['assets'];
	if($file != null) {
		$file = str_replace($nf_www_dir, '', $file);
		if(substr($file, 0, 1) !== '/') {
			$file = '/' . $file;
		}
		$uri .= $file;
	}
	return $uri;
}