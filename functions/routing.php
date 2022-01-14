<?php

$nf_routes = [];
$nf_uri_parts = [];

function nf_route($route, $action)
{
	global $nf_routes;
	$nf_routes[] = [
		'route' => $route,
		'action' => $action,
	];
}

function nf_handle_uri($uri)
{
	global $nf_routes;
	global $nf_uri_parts;

	$nf_uri_parts = explode('/', trim($uri, '/'));
	$numparts = count($nf_uri_parts);

	foreach($nf_routes as $route) {
		$routeparts = explode('/', trim($route['route'], '/'));
		$numrouteparts = count($routeparts);
		if($numrouteparts != $numparts) {
			continue;
		}

		$valid = true;
		$params = [];

		foreach($_GET as $k => $v) {
			$params[$k] = $v;
		}

		for($i = 0; $i < $numrouteparts; $i++) {
			$p = $nf_uri_parts[$i];
			$rp = $routeparts[$i];

			if(str_starts_with($rp, ':')) {
				$key = substr($rp, 1);
				$params[$key] = $p;
			} elseif($p != $rp) {
				$valid = false;
				break;
			}
		}

		if($valid) {
			nf_begin_page($route['action'], $params);
			return;
		}
	}

	if($uri == '/') {
		nf_begin_page(false, []);
		return;
	}

	nf_error_routing(18, $uri);
}

function nf_begin_page($route, $params)
{
	global $nf_cfg;

	$controller = $nf_cfg['index']['controller'];
	$action = $nf_cfg['index']['action'];

	if($route) {
		$actionparse = explode('.', $route);

		if(count($actionparse) == 1) {
			$controller = '';
			$action = $actionparse[0];
		} elseif(count($actionparse) == 2) {
			$controller = $actionparse[0];
			$action = $actionparse[1];
		}
	}

	if($controller != '') {
		nf_begin_action_method($controller, $action, $params);
	} else {
		nf_begin_action_function($action, $params);
	}
}

function nf_begin_get_callparams(?ReflectionFunctionAbstract $r, $params)
{
	if ($r === null) {
		return [];
	}

	$ret = [];

	$funcargs = $r->getParameters();
	foreach($funcargs as $arg) {
		$argname = $arg->getName();
		$argvalue = null;

		if(isset($params[$argname])) {
			$argvalue = $params[$argname];
		} elseif($arg->isDefaultValueAvailable()) {
			$argvalue = $arg->getDefaultValue();
		}

		if($argvalue === null) {
			nf_error(6, $r->getFileName() . ' (line ' . $r->getStartLine() . '), parameter ' . $argname);
			return null;
		}

		if($arg->hasType()) {
			switch($arg->getType()->getName()) {
				case 'int': $argvalue = intval($argvalue); break;
				case 'string': $argvalue = strval($argvalue); break;
				case 'bool':
					if($argvalue == 'false') {
						$argvalue = false;
					} else {
						$argvalue = boolval($argvalue);
					}
					break;
			}
		}

		$ret[] = $argvalue;
	}

	return $ret;
}

function nf_begin_action_method($controller, $action, $params)
{
	$r = new ReflectionClass($controller);

	$ctorparams = nf_begin_get_callparams($r->getConstructor(), $params);
	if($ctorparams === null) {
		return;
	}

	$c = $r->newInstanceArgs($ctorparams);

	$action = $c->beforeAction($action);
	if ($action === false) {
		return;
	}

	$actionfunc = 'action' . $action;

	$callparams = nf_begin_get_callparams($r->getMethod($actionfunc), $params);
	if($callparams === null) {
		return;
	}

	call_user_func_array([$c, $actionfunc], $callparams);
}

function nf_begin_action_function($action, $params)
{
	$r = new ReflectionFunction($action);

	$callparams = nf_begin_get_callparams($r, $params);
	if($callparams === null) {
		return;
	}

	call_user_func_array($action, $callparams);
}

function nf_handle_routing_rules($uri)
{
	global $nf_cfg;

	foreach($nf_cfg['routing']['rules'] as $regex => $route) {
		$matches = false;
		if(preg_match($regex, $uri, $matches)) {
			$action = '';
			$keys = array();
			foreach($matches as $k => $v) {
				if(is_string($k)) {
					if($k == '_action') {
						$action = $v;
					} else {
						$keys[$k] = $v;
					}
				}
			}
			$params = array_merge($_GET, $keys);
			return [$route, $params];
		}
	}

	return false;
}
