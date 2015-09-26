<?php

/*
 * Begin the framework.
 */
function nf_begin($dir, $options = array())
{
	global $nf_www_dir;
	global $nf_dir;
	global $nf_uri;
	global $nf_cfg;
	
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	
	nf_init_config($options);
	
	$nf_www_dir = $dir;
	$nf_dir = __DIR__;

	nf_i18n_init();

	if($nf_cfg['debug']['enabled']) {
		register_shutdown_function('nf_php_fatal');
		set_error_handler('nf_php_error');
		set_exception_handler('nf_php_exception');
		ini_set('display_errors', 'off');
		error_reporting(E_ALL);
	}

	if(!isset($nf_cfg['no_htaccess']) && !file_exists($dir . '/.htaccess')) {
		echo '<b>' . nf_t('Warning:') . '</b> ' . nf_t('.htaccess does not exist.');
		$ok = copy(__DIR__ . '/.htaccess', $dir . '/.htaccess');
		if($ok) {
			echo ' ' . nf_t('Nin was able to create it automatically for you. Refresh for it to take effect.') . '<br>';
		} else {
			echo ' ' . nf_t('Nin was not able to automatically create the file.');
			echo ' ' . nf_t('Please copy it manually from:') . ' <code>' . __DIR__ . '/.htaccess</code><br>';
		}
		echo ' ' . nf_t('To ignore this warning and stop this behavior, set \'no_htaccess\' in the config to true.');
		return;
	}
	
	nf_init_autoloader();
	
	if($nf_cfg['sql']['enabled']) {
		if(!nf_sql_connect(
			$nf_cfg['sql']['hostname'],
			$nf_cfg['sql']['username'],
			$nf_cfg['sql']['password'],
			$nf_cfg['sql']['database'],
			$nf_cfg['sql']['encoding'])) {
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

/**
 * Loads the language files for the currently active language.
 */
function nf_i18n_init()
{
	global $nf_www_dir;
	global $nf_i18n;
	global $nf_cfg;

	$lang = Nin::language();

	$nf_i18n = array();
	nf_i18n_loadtable(__DIR__ . '/' . $nf_cfg['paths']['i18n'] . '/' . $lang . '.php');
	nf_i18n_loadtable($nf_www_dir . '/' . $nf_cfg['paths']['i18n'] . '/' . $lang . '.php');
}

/**
 * Load a translation table from the given path.
 */
function nf_i18n_loadtable($path)
{
	global $nf_i18n;

	if(!file_exists($path)) {
		return;
	}

	$table = include($path);
	$nf_i18n = array_merge($nf_i18n, $table);
}

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
	
	// Look for models
	$filename = nf_autoload_find($nf_www_dir . '/' . $nf_cfg['paths']['models'] . '/', $classname);
	if($filename !== false) {
		include($filename);
		return;
	}
	
	// Look for components
	$filename = nf_autoload_find($nf_www_dir . '/' . $nf_cfg['paths']['components'] . '/', $classname);
	if($filename !== false) {
		include($filename);
		return;
	}
	
	// Look for internal validators
	$filename = nf_autoload_find($nf_dir . '/classes/validators/', $classname);
	if($filename !== false) {
		include($filename);
		return;
	}
	
	// If couldn't be found at all
	nf_error(8, $classname);
}

/**
 * Initialize the autoloader.
 */
function nf_init_autoloader()
{
	spl_autoload_register('nf_autoload');
}

/**
 * Translate a string.
 */
function nf_t($str, $params = array())
{
	global $nf_i18n;

	$ret = @$nf_i18n[$str];
	if($ret !== null) {
		foreach($params as $k => $v) {
			$ret = str_replace($k, $v, $ret);
		}
		return $ret;
	}

	return $str;
}

/**
 * Function that is called for all handled errors.
 */
function nf_error($num, $details = '')
{
	global $nf_cfg;

	$error = nf_t('Unknown');
	switch($num) {
		case 1: $error = nf_t('Invalid controller name'); break;
		case 2: $error = nf_t('Invalid action name'); break;
		case 3: $error = nf_t('Controller or module does not exist'); break;
		case 4: $error = nf_t('Controller class does not have the right name'); break;
		case 5: $error = nf_t('Action does not exist'); break;
		case 6: $error = nf_t('Action requires parameters not given'); break;
		case 7: $error = nf_t('Failed to connect to SQL database'); break;
		case 8: $error = nf_t('Class could not be found'); break;
		case 9: $error = nf_t('Table does not have a primary key'); break;
		case 10: $error = nf_t('SQL query failed'); break;
	}
	if($details != '') {
		$error .= ' (' . nf_t('Details:') . ' "' . $details . '")';
	}
	
	//TODO: Deprecate this and use nf_hook() for this!
	if($nf_cfg['error']['hook'] !== false) {
		$hook = $nf_cfg['error']['hook'];
		$hook($error);
	} else {
		echo nf_t('nf error:') . ' ' . $error . '<br>';
	}
}

/**
 * Error in resolving the routing path. This can be a fallback.
 * Called from routing functions such as nf_handle_uri() and nf_begin_page().
 */
function nf_error_routing($num, $details = '')
{
	global $nf_cfg;
	global $nf_uri;
	global $nf_uri_fallback;
	
	if($nf_uri_fallback) {
		nf_error($num, $details);
		return;
	}
	$nf_uri_fallback = true;
	
	if(!$nf_cfg['routing']['preferRules']) {
		$uri = nf_handle_routing_rules($nf_uri);
		if($uri !== $nf_uri) {
			$nf_uri = $uri;
			nf_handle_uri($nf_uri);
			return;
		}
	}
	
	nf_error($num, $details);
}

function nf_php_error($errno, $errstr, $errfile, $errline)
{
	nf_php_exception(new ErrorException($errstr, 0, $errno, $errfile, $errline));
}

/**
 * Called by PHP error reporting for fatal errors, warnings, and notices.
 * Not for syntax errors.
 */
function nf_php_exception($e)
{
	// if suppressed, don't care
	if(error_reporting() == 0) {
		return;
	}

	nf_hook('php-error', $e);

	$gl = function($fn, $l) {
		$ls = file($fn);
		return $ls[$l-1];
	};

	$sl = function($i, $func, $args, $fn, $l) use($gl) {
		echo '<tr style="background: #fdd;"><td style="border-top: 1px solid #faa;">' . $i . ' - ';
		if($func) {
			echo htmlentities($func) . '(';
			for($j = 0; $j < count($args); $j++) {
				echo '<code>';
				$arg = $args[$j];
				if(is_string($arg)) {
					echo '"';
					if(strlen($arg) > 50) {
						echo htmlentities(addslashes(substr($arg, 0, 47))) . '...';
					} else {
						echo htmlentities(addslashes($arg));
					}
					echo '"';
				} elseif(is_array($arg)) {
					echo 'array(' . count($arg) . ')';
				} elseif(is_bool($arg)) {
					echo $arg ? 'true' : 'false';
				} elseif(is_integer($arg)) {
					echo $arg;
				} else {
					echo gettype($arg);
				}
				echo '</code>';
				if($j != count($args) - 1) {
					echo ', ';
				}
			}
			echo ') ';
		}
		if($fn) {
			echo '<code>' . htmlentities($fn) . '</code> <i>(line ' . $l . ')</i></td>';
		}
		echo '</tr>';
		if($fn) {
			$line = $gl($fn, $l);
			$trim = false;
			$php = '';
			if(!strstr($line, '<?php') && !strstr($line, '?>') && !strstr($line, '<?=')) {
				$line = '<?php ' . $line;
				$trim = true;
			}
			$php = highlight_string($line, true);
			if($trim) {
				$php = str_replace('&lt;?php&nbsp;', '', $php);
			}
			echo '<tr style="background: #ddf;"><td>' . $php . '</td></tr>';
		}
	};
	echo '<table style="margin: 3em;" cellpadding="7" cellspacing="0">';
	echo '<tr bgcolor="#faa"><td><b>Exception:</b> ' . htmlentities($e->getMessage()) . '</td></tr>';
	$trace = $e->getTrace();
	for($i = 0; $i < count($trace); $i++) {
		$func = false;
		$args = false;
		$fnm = false;
		$ln = false;
		if(isset($trace[$i]['function'])) { $func = $trace[$i]['function']; }
		if(isset($trace[$i]['args'])) { $args = $trace[$i]['args']; }
		if(isset($trace[$i]['file'])) { $fnm = $trace[$i]['file']; }
		if(isset($trace[$i]['line'])) { $ln = $trace[$i]['line']; }
		$sl(1 + $i, $func, $args, $fnm, $ln);
	}
	echo '</table>';
}

function nf_php_fatal()
{
	$error = error_get_last();
	if($error['type'] == E_ERROR) {
		nf_php_error($error['type'], $error['message'], $error['file'], $error['line']);
	}
}

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
	
	$parts = array();
	$token = strtok($uri, '/');
	while($token !== false) {
		$parts[] = $token;
		$token = strtok('/');
	}
	
	$module = '/';
	$controller = $nf_cfg['index']['controller'];
	$action = $nf_cfg['index']['action'];
	
	$partcount = count($parts);

	for($i=0; $i<$partcount; $i++) {
		$part = $parts[$i];
		
		// Check if the part is a folder (that means there's a module)
		if(is_dir($nf_www_dir . '/' . $nf_cfg['paths']['controllers'] . $module . $part)) {
			// It is, so append this to the module path
			$module .= $part . '/';
			continue;
		}

		//Otherwise, it /should/ exist as a controller
		$controller = $part;
		// And, if it exists, the part that comes after that as the action
		if($i + 1 != $partcount) {
			$action = $parts[$i + 1];
		}
		// Now get ready to begin the page
		break;
	}
	
	$nf_module = $module;
	nf_begin_page($controller, $action, $parts);
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
			$keys = array();
			foreach($matches as $k => $v) {
				if(is_string($k)) {
					$keys[$k] = $v;
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
			return $ret;
		}
	}
	
	return $uri;
}

/**
 * Call a hook set in the config with the given name and parameters.
 */
function nf_hook($name, $params = array())
{
	global $nf_cfg;

	if(!isset($nf_cfg['hooks'])) {
		return null;
	}

	if(!isset($nf_cfg['hooks'][$name])) {
		return null;
	}

	$fn = $nf_cfg['hooks'][$name];
	return $fn($params);
}

/**
 * Begin the given page by controller name and action name.
 * This gets called from nf_handle_uri().
 */
function nf_begin_page($controllername, $actionname, $parts)
{
	global $nf_www_dir;
	global $nf_cfg;
	global $nf_module;

	if(!preg_match($nf_cfg['validation']['regex_controllers'], $controllername)) {
		nf_error_routing(1);
		return;
	}

	if(!preg_match($nf_cfg['validation']['regex_actions'], $actionname)) {
		nf_error_routing(2);
		return;
	}

	$filename = $nf_www_dir . '/' . $nf_cfg['paths']['controllers'] . $nf_module . $controllername . '.php';
	$classname = ucfirst($controllername) . 'Controller';
	if(file_exists($filename)) {
		if(!class_exists($classname, false)) {
			include($filename);
		}
	} else {
		nf_error_routing(3, $filename);
		return;
	}
	
	if(!class_exists($classname, false)) {
		nf_error_routing(4, $classname);
		return;
	}
	$controller = new $classname;
	$controller->uri_parts = $parts;
	
	$functionname = 'action' . ucfirst($actionname);

	$retbeforehookmod = false;
	$retbeforehook = nf_hook('before-action', array($actionname));
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
	
	$params = $m->getParameters();
	$args = array();
	foreach($params as $param) {
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
	call_user_func_array(array($controller, $functionname), $args);
}

/**
 * Connect to the SQL database.
 */
function nf_sql_connect($host, $user, $pass, $db, $encoding)
{
	global $nf_sql;
	if($nf_sql) {
		return false;
	}
	$nf_sql = new mysqli($host, $user, $pass, $db);
	$nf_sql->set_charset($encoding);
	return $nf_sql->connect_errno == 0;
}

/**
 * Perform a query on the SQL database.
 */
function nf_sql_query($query)
{
	global $nf_sql;
	$ret = $nf_sql->query($query);
	if($ret === false) {
		nf_error(10, nf_t('Error was:') . ' ' . $nf_sql->error . ' - ' . nf_t('Query was:') . ' ' . $query);
	}
	return $ret;
}

/**
 * Return the inserted ID.
 */
function nf_sql_insertid()
{
	global $nf_sql;
	return $nf_sql->insert_id;
}

/**
 * Escape the given string for SQL queries.
 */
function nf_sql_escape($str)
{
	global $nf_sql;
	return $nf_sql->real_escape_string($str);
}

/**
 * Encoding the given mixed-type object for a SQL query.
 */
function nf_sql_encode($o)
{
	if(is_string($o)) {
		return "'" . nf_sql_escape($o) . "'";
	}
	
	if(is_numeric($o)) {
		if(is_float($o)) {
			return str_replace(',', '.', strval(floatval($o)));
		}
		return intval($o);
	}
	
	return $o;
}

/**
 * Return the parameter from $nf_cfg['params'] with an optional default value if the param does not exist.
 * You can use periods to delimit between arrays.
 *
 * For example, you can access $nf_cfg['params']['test']['foo']['bar'] by using the key:
 *  "test.foo.bar"
 */
function nf_param($key, $default = null)
{
	global $nf_cfg;
	$keys = explode('.', $key);
	$ret = $nf_cfg['params'];
	foreach($keys as $k) {
		if(!isset($ret[$k])) {
			return $default;
		}
		$ret = $ret[$k];
	}
	return $ret;
}

function nf_xcopy($src, $dst, $ignore = array())
{
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ($file = readdir($dir))) {
		if($file != '.' && $file != '..') {
			if(is_dir($src . '/' . $file)) {
				nf_xcopy($src . '/' . $file, $dst . '/' . $file, $ignore);
			} elseif(array_search($src . '/' . $file, $ignore) === false) {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
} 
