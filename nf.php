<?php

// Configurable parameters
$nf_cfg_path_controllers = 'controllers';
$nf_cfg_path_views = 'views';
$nf_cfg_regex_controllers = '/^[a-z0-9\\-_]+$/';
$nf_cfg_regex_actions = '/^[a-z0-9_]+$/';

// Runtime paths to framework and content
$nf_dir = '';
$nf_www_dir = '';

include('nf_functions.php');
include('classes/controller.php');
