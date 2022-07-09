<?php

namespace Nin\Database;

abstract class Context
{
	/**
	 * Runs the given query and returns the result
	 * @return Result|bool
	 */
	public abstract function query(string $query);

	/**
	 * Begins building a new query
	 * @return QueryBuilder
	 */
	public abstract function beginBuild(string $table);

	/**
	 * Gets the schema of the table (a list of columns)
	 * @return SchemaColumn[]|bool
	 */
	public abstract function getSchema(string $table);
}
