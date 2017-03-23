<?php

/**
 * Connect to the SQL database.
 */
function nf_sql_connect($host, $user, $pass, $db, $encoding)
{
	global $nf_sql;
	if($nf_sql) {
		return false;
	}
	$nf_sql = new mysqli($host, $user, $pass, $db);
	$nf_sql->set_charset($encoding);
	return $nf_sql->connect_errno == 0;
}

/**
 * Perform a query on the SQL database.
 */
function nf_sql_query($query)
{
	global $nf_sql;
	$ret = $nf_sql->query($query);
	if($ret === false) {
		nf_error(10, nf_t('Error was:') . ' ' . $nf_sql->error . ' - ' . nf_t('Query was:') . ' ' . $query);
	}
	return $ret;
}

/**
 * Return the inserted ID.
 */
function nf_sql_insertid()
{
	global $nf_sql;
	return $nf_sql->insert_id;
}

/**
 * Escape the given string for SQL queries.
 */
function nf_sql_escape($str)
{
	global $nf_sql;
	return $nf_sql->real_escape_string($str);
}

/**
 * Encoding the given mixed-type object for a SQL query.
 */
function nf_sql_encode($o)
{
	if(is_string($o)) {
		return "'" . nf_sql_escape($o) . "'";
	}

	if(is_numeric($o)) {
		if(is_float($o)) {
			return str_replace(',', '.', strval(floatval($o)));
		}
		return intval($o);
	}

	return $o;
}
