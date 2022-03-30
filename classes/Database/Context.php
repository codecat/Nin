<?php

namespace Nin\Database;

abstract class Context
{
	/**
	 * Runs the given query and returns the result
	 * @return Result
	 */
	public abstract function query($query);

	/**
	 * Begins building a new query
	 * @return QueryBuilder
	 */
	public abstract function beginBuild($table);
}
