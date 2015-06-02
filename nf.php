<?php

// Configurable parameters:
	// Paths to places
	$nf_cfg_path_controllers = 'controllers';
	$nf_cfg_path_views = 'views';
	$nf_cfg_path_models = 'models';
	
	// Validation regexes
	$nf_cfg_regex_controllers = '/^[a-z0-9\\-_]+$/';
	$nf_cfg_regex_actions = '/^[a-z0-9_]+$/';
	
	// SQL information
	$nf_cfg_sql_enabled = false;
	$nf_cfg_sql_hostname = 'localhost';
	$nf_cfg_sql_username = '';
	$nf_cfg_sql_password = '';
	$nf_cfg_sql_database = '';
// End of configurable parameters

// Runtime paths to framework and content
$nf_dir = '';
$nf_www_dir = '';

// Runtime SQL variables
$nf_sql = false;

// Include dependencies
include('nf_functions.php');
include('classes/controller.php');
include('classes/model.php');
