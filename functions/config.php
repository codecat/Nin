<?php

$nf_cfg = [
	// Website title
	'name' => 'Nin',

	// Paths to places
	'paths' => [
		'base' => '/',
		'controllers' => 'controllers',
		'views' => 'views',
		'models' => 'models',
		'components' => 'components',
		'i18n' => 'i18n',
		'validators' => 'validators',
		'logs' => 'logs',
	],

	// IP address maximum forwarded for count (set to -1 to disable)
	'forwarded_for_max' => 2,

	// Routing configuration
	'routing' => [
		'notfound' => false,
		'preferRules' => true,
		'rules' => [],
	],

	// Index pages
	'index' => [
		'controller' => 'IndexController',
		'action' => 'index',
	],

	// Validation
	'validation' => [
		'parameters_exclusive' => true,
	],

	// Database information
	'db' => [
		'class' => 'Dummy',
		'options' => [],
	],

	// User authentication
	'user' => [
		'model' => 'User',
	],

	// Caching information
	'cache' => [
		'class' => 'Dummy',
		'options' => [],
	],

	// Rendering
	'render' => [
		'ext' => '.php',
		'cache' => '/tmp/nin_render_cache',
		'debug' => false,
	],

	// Hooks
	'hooks' => [],

	// Internationalization
	'i18n' => [
		'languages' => ['en-US'],
		'language' => 'en-US',
	],

	// Debugging
	'debug' => [
		'enabled' => false,
	],

	// Additional parameters
	'params' => [],
];

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

		if(is_array($v)) {
			$nf_cfg[$k] = array_merge($nf_cfg[$k], $v);
		} else {
			$nf_cfg[$k] = $v;
		}
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
