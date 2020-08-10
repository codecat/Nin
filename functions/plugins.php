<?php

$nf_plugins = array();

/**
 * Initialize any plugins defined in the configuration.
 */
function nf_plugins_initialize()
{
	global $nf_cfg;
	global $nf_www_dir;
	global $nf_plugins;

	foreach ($nf_cfg['plugins'] as $plugin) {
		$path = '';
		if (strpos($plugin, './') === 0) {
			$path = $nf_www_dir . '/' . substr($plugin, 2);
		} else {
			$path = $nf_www_dir . '/plugins/' . $plugin;
		}

		if (!file_exists($path . '/plugin.php')) {
			nf_error(16, $path);
			continue;
		}

		$info = require_once($path . '/plugin.php');

		$nf_plugins[] = array(
			'id' => basename($plugin),
			'info' => $info,
		);
	}
}
