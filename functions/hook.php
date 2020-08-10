<?php

/**
 * Call a hook set in the config or a plugin with the given name and parameters.
 */
function nf_hook($name, $params = array())
{
	global $nf_cfg;
	global $nf_plugins;

	$ret = array();

	if (isset($nf_cfg['hooks'])) {
		if (isset($nf_cfg['hooks'][$name])) {
			$ret[] = $nf_cfg['hooks'][$name]($params);
		}
	}

	foreach ($nf_plugins as $plugin) {
		$info = $plugin['info'];
		if (!isset($info['hooks'])) {
			continue;
		}

		if (isset($info['hooks'][$name])) {
			$ret[] = $info['hooks'][$name]($params);
		}
	}

	return $ret;
}

/**
 * Calls a hook, but is guaranteed to return only one result.
 * It's kind of dumb, as it will just return the first non-null result.
 */
function nf_hook_one($name, $params)
{
	$ret = nf_hook($name, $params);
	foreach ($ret as $val) {
		if ($val !== null) {
			return $val;
		}
	}
	return null;
}
