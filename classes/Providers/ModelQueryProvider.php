<?php

namespace Nin\Providers;

use Nin\Providers\QueryProvider;
use Nin\Database\ModelQueryBuilder;

class ModelQueryProvider extends QueryProvider
{
	public $class;

	public function __construct(string $class, $builder)
	{
		parent::__construct($builder);
		$this->class = $class;
	}

	public function getNext()
	{
		$row = parent::getNext();
		if (!$row) {
			return null;
		}

		$ret = new $this->class();
		$ret->loadRow($row);
		return $ret;
	}
}
