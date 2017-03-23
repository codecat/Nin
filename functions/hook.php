<?php

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
