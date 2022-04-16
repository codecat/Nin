<?php

/**
 * Function that is called for all handled errors.
 */
function nf_error($num, $details = '', $code = 500)
{
	$error = nf_t('Unknown');
	switch($num) {
		case 1: $error = nf_t('Invalid controller name'); break;
		case 2: $error = nf_t('Invalid action name'); break;
		case 3: $error = nf_t('Controller or module does not exist'); break;
		case 4: $error = nf_t('Controller class does not have the right name'); break;
		case 5: $error = nf_t('Action does not exist'); break;
		case 6: $error = nf_t('Begin call requires parameters not given'); break;
		case 7: $error = nf_t('Failed to connect to database'); break;
		case 8: $error = nf_t('Class could not be found'); break;
		case 9: $error = nf_t('Table does not have a primary key'); break;
		case 10: $error = nf_t('Database query failed'); break;
		case 11: $error = nf_t('ListView tried to render without a provider'); break;
		case 12: $error = nf_t('Database context class does not exist'); break;
		case 13: $error = nf_t('Cache class does not exist'); break;
		case 14: $error = nf_t('A required module is not installed'); break;
		case 15: $error = nf_t('A suitable renderer could not be found for the view'); break;
		case 17: $error = nf_t('Controller requires parameters not given'); break;
		case 18: $error = nf_t('Route does not exist'); break;
	}
	if($details != '') {
		$error .= ' (' . nf_t('Details:') . ' "' . Nin\Html::encode($details) . '")';
	}

	header('HTTP/1.1 ' . $code);
	if(nf_hook_one('error', [$num, $details, $error]) === null) {
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
		$routeaction = nf_handle_routing_rules($nf_uri);
		if($routeaction !== false) {
			nf_begin_page($routeaction[0], $routeaction[1]);
			return;
		}
	}

	if($nf_cfg['routing']['notfound'] !== false) {
		nf_begin_page($nf_cfg['routing']['notfound'], ['uri' => $nf_uri]);
		return;
	}

	nf_error($num, $details, 404);
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

	$trace = $e->getTrace();

	echo "<pre style=\"background: #fee; color: #111; font-size: 12px; border: 1px solid #f00; padding: 5px;\">\n<b>";
	echo get_class($e) . '</b>: ' . Nin\Html::encode($e->getMessage()) . "\n";
	echo 'in <u>' . $e->getFile() . '</u> on line ' . $e->getLine() . "\n";
	for($i = 0; $i < count($trace); $i++) {
		$fnm = ''; $ln = '';
		if(isset($trace[$i]['file'])) { $fnm = $trace[$i]['file']; }
		if(isset($trace[$i]['line'])) { $ln = $trace[$i]['line']; }
		echo "   <u>" . $fnm . '</u>';
		if ($ln !== '') {
			echo ' on line ' . $ln;
		}
		echo "\n";
	}
	echo '</pre>';
}

function nf_php_fatal()
{
	$error = error_get_last();
	if($error && $error['type'] == E_ERROR) {
		nf_php_error($error['type'], $error['message'], $error['file'], $error['line']);
	}
}
