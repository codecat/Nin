<?php

// Configurable parameters (pass array to nf_begin() to merge this)
$nf_cfg = array(
	// Paths to places
	'paths' => array(
		'base' => '/',
		'controllers' => 'controllers',
		'views' => 'views',
		'models' => 'models',
		'components' => 'components',
		'i18n' => 'i18n'
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
	
	// SQL information
	'sql' => array(
		'enabled' => false,
		'hostname' => 'localhost',
		'username' => '',
		'password' => '',
		'database' => ''
	),

	// Error hooking
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
// End of configurable parameters

// Runtime paths to framework and content
$nf_dir = '';
$nf_www_dir = '';
$nf_uri = '';
$nf_uri_fallback = false;

// Runtime SQL variables
$nf_sql = false;

// Internationalization cache
$nf_i18n = array();

// Include dependencies
include('nf_define.php');
include('nf_functions.php');
include('classes/controller.php');
include('classes/model.php');
include('classes/nin.php');
include('classes/html.php');
include('classes/validator.php');
include('classes/log.php');
include('classes/cache.php');
