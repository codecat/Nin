<?php

/*
 * Begin the framework.
 */
function nf_begin($dir)
{
	global $nf_www_dir;
	global $nf_dir;
	global $nf_cfg_sql_enabled;
	global $nf_cfg_sql_hostname;
	global $nf_cfg_sql_username;
	global $nf_cfg_sql_password;
	global $nf_cfg_sql_database;
	
	$nf_www_dir = $dir;
	$nf_dir = __DIR__;
	
	$uri = $_SERVER['REQUEST_URI'];
	$uri_part = strstr($uri, '?', true);
	if($uri_part) {
		$uri = $uri_part;
	}
	nf_handle_uri($uri);
	
	if($nf_cfg_sql_enabled) {
		if(!nf_sql_connect($nf_cfg_sql_hostname, $nf_cfg_sql_username, $nf_cfg_sql_password, $nf_cfg_sql_database)) {
			nf_error(7);
		}
	}
}

/**
 * Translate a string.
 */
function nf_t($str)
{
	//TODO
	return $str;
}

/**
 * Function that is called for all handled errors.
 */
function nf_error($num)
{
	$error = nf_t('Unknown');
	switch($num) {
		case 1: $error = nf_t('Invalid controller name'); break;
		case 2: $error = nf_t('Invalid action name'); break;
		case 3: $error = nf_t('Controller does not exist'); break;
		case 4: $error = nf_t('Controller class does not have the right name'); break;
		case 5: $error = nf_t('Action does not exist'); break;
		case 6: $error = nf_t('Action requires parameters not given'); break;
		case 7: $error = nf_t('Failed to connect to SQL database'); break;
	}
	
	//TODO, hook?
	echo 'nf error: ' . $error;
}

/**
 * Handle the REQUEST_URI (without the URL params) and invoke the controller/action.
 * This gets called from nf_begin().
 */
function nf_handle_uri($uri)
{
	global $nf_cfg_regex_controllers;
	global $nf_cfg_regex_actions;
	
	$parts = array();
	$token = strtok($uri, '/');
	while($token !== false) {
		$parts[] = $token;
		$token = strtok('/');
	}
	
	$controller = 'index';
	$action = 'index';
	
	$partcount = count($parts);
	
	if($partcount >= 1) {
		$controller = strtolower($parts[0]);
		if(!preg_match($nf_cfg_regex_controllers, $controller)) {
			nf_error(1);
			return;
		}
	}
	
	if($partcount >= 2) {
		$action = strtolower($parts[1]);
		if(!preg_match($nf_cfg_regex_actions, $action)) {
			nf_error(2);
			return;
		}
	}
	
	nf_begin_page($controller, $action);
}

/**
 * Begin the given page by controller name and action name.
 * This gets called from nf_handle_uri().
 */
function nf_begin_page($controllername, $actionname)
{
	global $nf_www_dir;
	global $nf_cfg_path_controllers;
	
	$filename = $nf_www_dir . '/' . $nf_cfg_path_controllers . '/' . $controllername . '.php';
	if(file_exists($filename)) {
		include($filename);
	} else {
		nf_error(3);
		return;
	}
	
	$classname = ucfirst($controllername) . 'Controller';
	if(!class_exists($classname)) {
		nf_error(4);
		return;
	}
	$controller = new $classname;
	
	$functionname = 'action' . ucfirst($actionname);
	if(!method_exists($controller, $functionname)) {
		nf_error(5);
		return;
	}
	
	$r = new ReflectionClass($classname);
	$m = $r->getMethod($functionname);
	
	$params = $m->getParameters();
	$args = array();
	foreach($params as $param) {
		if(isset($_REQUEST[$param->name])) {
			$args[] = $_REQUEST[$param->name];
		} else {
			if($param->isDefaultValueAvailable()) {
				$args[] = $param->getDefaultValue();
			} else {
				nf_error(6);
				return;
			}
		}
	}
	call_user_func_array(array($controller, $functionname), $args);
}

/**
 * Connect to the SQL database.
 */
function nf_sql_connect($host, $user, $pass, $db)
{
	global $nf_sql;
	if($nf_sql) {
		return false;
	}
	$nf_sql = new mysqli($host, $user, $pass, $db);
	return $nf_sql->connect_errno == 0;
}

/**
 * Perform a query on the SQL database.
 */
function nf_sql_query($query)
{
	global $nf_sql;
	return $nf_sql->query($query);
}
