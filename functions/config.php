<?php

$nf_cfg = array(
	// Paths to places
	'paths' => array(
		'base' => '/',
		'controllers' => 'controllers',
		'views' => 'views',
		'models' => 'models',
		'components' => 'components',
		'i18n' => 'i18n',
		'validators' => 'validators',
		'assets' => 'assets',
		'logs' => 'logs',
	),

	// Routing configuration
	'routing' => array(
		'preferRules' => true,
		'rules' => array()
	),

	// Index pages
	'index' => array(
		'controller' => 'index',
		'action' => 'index'
	),

	// Validation
	'validation' => array(
		'regex_controllers' => '/^[a-z0-9\\-_]+$/',
		'regex_actions' => '/^[a-z0-9_]+$/',
		'parameters_exclusive' => true,
	),

	// Database information
	'db' => array(
		'class' => 'Dummy',
		'options' => array(),
	),

	// Caching information
	'cache' => array(
		'class' => 'Dummy',
		'options' => array(),
	),

	// Error hooking (TODO: Move this to regular hooks)
	'error' => array(
		'hook' => false
	),

	// Hooks
	'hooks' => array(),

	// Internationalization
	'i18n' => array(
		'languages' => array('en-US'),
		'language' => 'en-US'
	),

	// Debugging
	'debug' => array(
		'enabled' => true,
	),

	// Additional parameters
	'params' => array()
);

/**
 * Called by nf_begin() to merge the given options with $nf_cfg.
 */
function nf_config_initialize($options)
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
