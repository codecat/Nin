<?php

namespace Nin\Database\QueryBuilders;

use Nin\Database\QueryBuilder;

class Dummy extends QueryBuilder
{
	public function build()
	{
		return false;
	}
}
