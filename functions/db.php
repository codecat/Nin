<?php

/**
 * Global database context
 * @var Nin\Database\Context|false
 */
$nf_db_context = false;

/**
 * Creates the database context from the class name given in the configuration.
 * Gets called from nf_begin_internal().
 */
function nf_db_initialize()
{
	global $nf_cfg;
	global $nf_db_context;

	if(isset($nf_cfg['mysql'])) {
		$nf_cfg['db']['class'] = 'MySQL';
		$nf_cfg['db']['options'] = $nf_cfg['mysql'];
	} elseif(isset($nf_cfg['postgres'])) {
		$nf_cfg['db']['class'] = 'Postgres';
		$nf_cfg['db']['options'] = $nf_cfg['postgres'];
	} elseif(isset($nf_cfg['sqlite'])) {
		$nf_cfg['db']['class'] = 'SQLite';
		if (is_string($nf_cfg['sqlite'])) {
			$nf_cfg['db']['options'] = [
				'path' => $nf_cfg['sqlite'],
			];
		} else {
			$nf_cfg['db']['options'] = $nf_cfg['sqlite'];
		}
	}

	$class_name = $nf_cfg['db']['class'];
	$class_name_internal = 'Nin\\Database\\Contexts\\' . $class_name;

	$options = $nf_cfg['db']['options'];

	if(class_exists($class_name)) {
		$nf_db_context = new $class_name($options);
	} elseif(class_exists($class_name_internal)) {
		$nf_db_context = new $class_name_internal($options);
	} else {
		nf_error(12, $class_name . ', ' . $class_name_internal);
		return;
	}
}

/**
 * Helper function for getting the global database context.
 */
function nf_db_context()
{
	global $nf_db_context;
	return $nf_db_context;
}

/**
 * Helper function for beginning query build on the active context.
 */
function nf_db_beginbuild($table)
{
	global $nf_db_context;
	return $nf_db_context->beginBuild($table);
}
