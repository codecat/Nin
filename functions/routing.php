<?php

$nf_module = '';
$nf_current_controllername = '';

/**
 * Handle the REQUEST_URI (without the URL params) and invoke the controller/action.
 * This gets called from nf_begin().
 */
function nf_handle_uri($uri)
{
	global $nf_cfg;
	global $nf_uri;
	global $nf_www_dir;
	global $nf_module;

	$nf_uri = $uri = substr($uri, strlen($nf_cfg['paths']['base'])-1);
	if($nf_cfg['routing']['preferRules']) {
		$nf_uri = $uri = nf_handle_routing_rules($uri);
	}

	$parts = [];
	$token = strtok($uri, '/');
	while($token !== false) {
		$parts[] = $token;
		$token = strtok('/');
	}

	$module = '/';
	$controller = $nf_cfg['index']['controller'];
	$action = $nf_cfg['index']['action'];

	$partcount = count($parts);
	$lastpart = 0;

	for ($i = 0; $i < $partcount; $i++) {
		$part = $parts[$i];

		// Check if the part is a folder (that means there's a module)
		if(is_dir($nf_www_dir . '/' . $nf_cfg['paths']['controllers'] . $module . $part)) {
			// It is, so append this to the module path
			$module .= strtolower($part) . '/';
			continue;
		}

		// Otherwise, it /should/ exist as a controller
		$controller = strtolower($part);
		// And, if it exists, the part that comes after that as the action
		if($i + 1 != $partcount) {
			$action = strtolower($parts[$i + 1]);
		}
		$lastpart = $i + 1;
		// Now get ready to begin the page
		break;
	}

	$nf_module = $module;

	nf_begin_page($controller, $action, $parts, $lastpart);
}

/**
 * Handle the REQUEST_URI (without the URL params) bsaed on the custom routing rules.
 * This returns true if there's a handled routing rule, or false if not.
 * This gets called from nf_handle_uri().
 */
function nf_handle_routing_rules($uri)
{
	global $nf_cfg;

	foreach($nf_cfg['routing']['rules'] as $regex => $route) {
		$matches = false;
		if(preg_match($regex, $uri, $matches)) {
			$action = '';
			$keys = [];
			foreach($matches as $k => $v) {
				if(is_string($k)) {
					if($k == '_action') {
						$action = $v;
					} else {
						$keys[$k] = $v;
					}
				}
			}
			$_GET = array_merge($_GET, $keys);
			$_REQUEST = array_merge($_REQUEST, $keys);
			$ret = $route;
			if(strstr($ret, '$')) {
				foreach($matches as $k => $v) {
					$ret = str_replace('$' . $k, $v, $ret);
				}
			}
			if ($action != '') {
				$ret .= '/' . $action;
			}
			return $ret;
		}
	}

	return $uri;
}

/**
 * Begin the given page by controller name and action name.
 * This gets called from nf_handle_uri().
 */
function nf_begin_page($controllername, $actionname, $parts, $lastpart)
{
	global $nf_www_dir;
	global $nf_cfg;
	global $nf_module;
	global $nf_current_controllername;
	global $nf_using_controllers;

	if(!preg_match($nf_cfg['validation']['regex_controllers'], $controllername)) {
		nf_error_routing(1);
		return;
	}

	$nf_current_controllername = $controllername;

	$folder = $nf_www_dir . '/' . $nf_cfg['paths']['controllers'] . $nf_module;
	if(!file_exists($folder) && !$nf_using_controllers) {
		return;
	}

	$classname = ucfirst($controllername) . 'Controller';
	if(!class_exists($classname, false)) {
		$filename = $folder . $controllername . '.php';
		if(file_exists($filename)) {
			include($filename);
		} else {
			// Maybe it's the exact classname
			$filename_classname = $folder . $classname . '.php';
			if(file_exists($filename_classname)) {
				include($filename_classname);
			} else {
				// Maybe it exists with ucfirst
				$filename_lower = $folder . ucfirst($controllername) . '.php';
				if(file_exists($filename_lower)) {
					include($filename_lower);
				} else {
					nf_error_routing(3, substr($filename, strlen($nf_www_dir) + 1));
					return;
				}
			}
		}
	}

	if(!class_exists($classname, false)) {
		nf_error_routing(4, $classname);
		return;
	}

	$controller = false;

	$ctorParams = [];
	$r = new ReflectionClass($classname);
	$cm = $r->getConstructor();
	if($cm) {
		$ctorParams = $cm->getParameters();
	}

	if(count($ctorParams) == 0) {
		$controller = new $classname;
	} else {
		$args = [];
		for($i = 0; $i < count($ctorParams); $i++) {
			$param = $ctorParams[$i];
			$j = $lastpart + $i;
			if($j < count($parts)) {
				$args[] = $parts[$j];
			} else {
				if($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					nf_error(17, $param->getName());
					return;
				}
			}
		}
		$lastpart += count($ctorParams);

		if($lastpart >= count($parts)) {
			$actionname = $nf_cfg['index']['action'];
		} else {
			$actionname = $parts[$lastpart];
		}

		$controller = $r->newInstanceArgs($args);
	}

	$controller->uri_parts = $parts;

	if(!preg_match($nf_cfg['validation']['regex_actions'], $actionname)) {
		nf_error_routing(2);
		return;
	}

	$functionname = 'action' . ucfirst($actionname);

	$retbeforehookmod = false;
	$retbeforehook = nf_hook_one('before-action', [$actionname]);
	if($retbeforehook !== null) {
		if($retbeforehook === false) {
			return;
		} else {
			$functionname = 'action' . ucfirst($retbeforehook);
		}
	}

	$retbefore = $controller->beforeAction($actionname);
	if($retbefore === false) {
		return;
	} else {
		$functionname = 'action' . ucfirst($retbefore);
	}

	if(!method_exists($controller, $functionname)) {
		nf_error_routing(5, $functionname);
		return;
	}

	$r = new ReflectionClass($classname);
	$m = $r->getMethod($functionname);

	$urlparams = array_slice($parts, $lastpart + 1);
	$args = $urlparams;

	$params = $m->getParameters();
	for ($i = count($urlparams); $i < count($params); $i++) {
		$param = $params[$i];
		if(isset($_REQUEST[$param->getName()])) {
			$args[] = $_REQUEST[$param->getName()];
		} else {
			if($param->isDefaultValueAvailable()) {
				$args[] = $param->getDefaultValue();
			} else {
				nf_error(6, $param->getName());
				return;
			}
		}
	}
	call_user_func_array([$controller, $functionname], $args);
}
