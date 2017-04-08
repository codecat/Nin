<?php

namespace Nin\Database;

class ModelQueryBuilder
{
	public $class;
	public $builder;

	public function __construct($class)
	{
		$this->class = $class;
		$this->builder = nf_db_beginbuild($class::tablename());
	}

	public function findAll()
	{
		return $this->class::findAllByResult($this->builder->execute());
	}

	public function find()
	{
		return $this->class::findByResult($this->builder->execute());
	}

	public function __call($name, $arguments)
	{
		call_user_func_array(array($this->builder, $name), $arguments);
		return $this;
	}
}
