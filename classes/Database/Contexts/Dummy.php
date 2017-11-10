<?php

namespace Nin\Database\Contexts;

use Nin\Database\Context;

class Dummy extends Context
{
	public function __construct($options)
	{
	}

	public function query($query) { return false; }
	public function beginBuild($table) { return new \Nin\Database\QueryBuilders\Dummy($this, $table); }
}
