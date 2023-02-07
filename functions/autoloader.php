<?php

/**
 * Used by nf_autoload() to find an appropriate filename for the class.
 */
function nf_autoload_find($path, $classname)
{
	$parse = explode('\\', $classname);
	if(count($parse) > 1) {
		for($i = 0; $i < count($parse) - 1; $i++) {
			if($i == 0 && $parse[$i] == 'Nin') {
				continue;
			}
			$path .= $parse[$i] . '/';
		}
	}
	$classname = $parse[count($parse) - 1];

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
	global $nf_project_dir;
	global $nf_dir;

	$paths = ['/'];

	// Use manual path lookups when not using namespaces
	if(strstr($classname, '\\') !== false) {
		// Look for classes
		foreach($paths as $module) {
			$filename = nf_autoload_find($nf_project_dir . $module, $classname);
			if($filename !== false) {
				include($filename);
				return;
			}
		}
	} else {
		// Look for controllers
		foreach($paths as $module) {
			$filename = nf_autoload_find($nf_project_dir . '/controllers' . $module, $classname);
			if($filename !== false) {
				include($filename);
				return;
			}
		}

		// Look for models
		foreach($paths as $module) {
			$filename = nf_autoload_find($nf_project_dir . '/models' . $module, $classname);
			if($filename !== false) {
				include($filename);
				return;
			}
		}

		// Look for components
		foreach($paths as $module) {
			$filename = nf_autoload_find($nf_project_dir . '/components' . $module, $classname);
			if($filename !== false) {
				include($filename);
				return;
			}
		}
	}

	// Look for internal classes
	$filename = nf_autoload_find($nf_dir . '/classes/', $classname);
	if($filename !== false) {
		include($filename);
		return;
	}

	// If couldn't be found at all
	//nf_error(8, $classname);
}
