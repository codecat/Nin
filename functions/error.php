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

	header('HTTP/1.1 500');

	nf_hook('php-error', $e);

	$gl = function($fn, $l) {
		$ls = file($fn);
		return $ls[$l-1];
	};

	$sl = function($i, $func, $args, $fn, $l) use($gl) {
		echo '<tr style="background: #fdd;"><td style="border-top: 1px solid #faa;">';
		if($func) {
			echo htmlentities($func) . '(';
			if ($args !== false) {
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
						echo '[..' . count($arg) . ']';
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
	echo '<tr bgcolor="#faa"><td><b>' . get_class($e) . ':</b> ' . htmlentities($e->getMessage());
	if(is_a($e, 'ParseError')) {
		echo '<br>in <b>' . $e->getFile() . '</b> on line <b>' . $e->getLine() . '</b>';
	}
	echo '</td></tr>';
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

	echo "<!--\nException in plain text:\n";
	echo get_class($e) . ': ' . $e->getMessage() . "\n";
	echo 'in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n";
	for($i = 0; $i < count($trace); $i++) {
		$func = '';
		$fnm = '';
		$ln = '';
		if(isset($trace[$i]['function'])) { $func = $trace[$i]['function']; }
		if(isset($trace[$i]['file'])) { $fnm = $trace[$i]['file']; }
		if(isset($trace[$i]['line'])) { $ln = $trace[$i]['line']; }
		echo "\t" . $func;
		if ($fnm !== '') {
			echo "\t\t" . $fnm;
		}
		if ($ln !== '') {
			echo "\t\t(line " . $ln . ')';
		}
		echo "\n";
	}
	echo "-->\n";
}

function nf_php_fatal()
{
	$error = error_get_last();
	if($error && $error['type'] == E_ERROR) {
		nf_php_error($error['type'], $error['message'], $error['file'], $error['line']);
	}
}
