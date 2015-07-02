#!/usr/bin/env php
<?php
/**
 * Nin from CLI 
 */

include('nf.php');

// Formatting codes
define('C_RESET', "\033[0m");

define('C_BOLD', "\033[1m");
define('C_DARK', "\033[2m");
define('C_ITALIC', "\033[3m");
define('C_UNDERLINE', "\033[4m");
define('C_FLIP', "\033[7m");
define('C_STRIKE', "\033[9m");

define('C_GRAY', "\033[30m");
define('C_RED', "\033[31m");
define('C_GREEN', "\033[32m");
define('C_YELLOW', "\033[33m");
define('C_BLUE', "\033[34m");
define('C_PURPLE', "\033[35m");
define('C_CYAN', "\033[36m");
define('C_WHITE', "\033[37m");

define('C_BG_GRAY', "\033[40m");
define('C_BG_RED', "\033[41m");
define('C_BG_GREEN', "\033[42m");
define('C_BG_YELLOW', "\033[43m");
define('C_BG_BLUE', "\033[44m");
define('C_BG_PURPLE', "\033[45m");
define('C_BG_CYAN', "\033[46m");
define('C_BG_WHITE', "\033[47m");

$actions = array(
	'help' => 'Get this screen.',
	'create' => 'Create a new site skeleton.',
);

function actionHelp($args)
{
	global $actions;

	$longestaction = 0;
	foreach(array_keys($actions) as $k) {
		$len = strlen($k);
		if($len > $longestaction) {
			$longestaction = $len;
		}
	}

	echo C_BOLD . C_BLUE . 'Nin v' . NF_VERSION . " CLI\n\n" . C_RESET;

	foreach($actions as $k => $v) {
		echo '  ' . C_BOLD . C_GREEN . $k . C_RESET . str_repeat(' ', ($longestaction + 2) - strlen($k)) . $v . "\n";
	}
}

function actionCreate($args)
{
	if(count($args) == 0) {
		echo "Usage:\n\n";
		echo '  ' . C_BOLD . C_GREEN . 'create' . C_RESET . ' ' . C_BLUE . '<path>' . C_RESET . "\n";
		return;
	}

	nf_xcopy('skeleton', $args[0], array('skeleton/index.php'));
	$index = file_get_contents('skeleton/index.php');
	$index = str_replace('/*NF_PHP*/', '\'' . addslashes(__DIR__ . '/nf.php') . '\'', $index);
	file_put_contents($args[0] . '/index.php', $index);

	echo C_BOLD . C_GREEN . 'Done!' . C_RESET . ' Skeleton project created in ' . C_BLUE . $args[0] . C_RESET . "\n";
}

echo "\n";

$args = array_slice($argv, 1);
if(count($args) == 0) {
	actionHelp(array());
} else {
	$action = $args[0];
	$function = 'action' . ucfirst($action);
	if(function_exists($function)) {
		$function(array_slice($args, 1));
	} else {
		echo C_RED . 'No such action.';
	}
}

echo "\n";
