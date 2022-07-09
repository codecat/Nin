<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;

class Dummy extends Context
{
	public function __construct($options)
	{
	}

	public function query(string $query) { return false; }
	public function beginBuild(string $table) { return new \Nin\Database\QueryBuilders\Dummy($this, $table); }
	public function getSchema(string $table) { return false; }
}
