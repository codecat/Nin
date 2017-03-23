<?php

/**
 * Used by nf_autoload() to find an appropriate filename for the class.
 */
function nf_autoload_find($path, $classname)
{
	$test = $path . $classname . '.php';
	if(file_exists($test)) return $test;

	$test = $path . ucfirst($classname) . '.php';
	if(file_exists($test)) return $test;

	$test = $path . strtolower($classname) . '.php';
	if(file_exists($test)) return $test;

	return false;
}

/**
 * __autoload() implementation.
 */
function nf_autoload($classname)
{
	global $nf_www_dir;
	global $nf_dir;
	global $nf_cfg;
	global $nf_module;

	$parse = explode('/', trim($nf_module, '/'));
	$paths = array('/');
	$pathStart = '/';
	for($i=0; $i<count($parse); $i++) {
		if(empty($parse[$i])) {
			continue;
		}
		$paths[] = $pathStart . $parse[$i] . '/';
		$pathStart .= $parse[$i] . '/';
	}

	// Look for models
	foreach($paths as $module) {
		$filename = nf_autoload_find($nf_www_dir . '/' . $nf_cfg['paths']['models'] . $module, $classname);
		if($filename !== false) {
			include($filename);
			return;
		}
	}

	// Look for components
	foreach($paths as $module) {
		$filename = nf_autoload_find($nf_www_dir . '/' . $nf_cfg['paths']['components'] . $module, $classname);
		if($filename !== false) {
			include($filename);
			return;
		}
	}

	// Look for internal validators
	$filename = nf_autoload_find($nf_dir . '/classes/validators/', $classname);
	if($filename !== false) {
		include($filename);
		return;
	}

	// Look for user validators
	foreach($paths as $module) {
		$filename = nf_autoload_find($nf_www_dir . '/' . $nf_cfg['paths']['validators'] . $module, $classname);
		if($filename !== false) {
			include($filename);
			return;
		}
	}

	// If couldn't be found at all
	//nf_error(8, $classname);
}

/**
 * Initialize the autoloader.
 */
function nf_init_autoloader()
{
	spl_autoload_register('nf_autoload');
}
