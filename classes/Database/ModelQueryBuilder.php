<?php

namespace Nin\Database;

class ModelQueryBuilder
{
	public $class;
	public $builder;

	public function __construct(string $class)
	{
		$this->class = $class;
		$this->builder = nf_db_beginbuild($class::tablename());
		$this->builder->returning($class::primarykey());
	}

	/**
	 * Runs the query and returns all model objects
	 * @return \Nin\Model[]
	 */
	public function findAll()
	{
		return $this->class::findAllByResult($this->builder->execute());
	}

	/**
	 * Runs the query and returns the first model object
	 * @return \Nin\Model
	 */
	public function find()
	{
		return $this->class::findByResult($this->builder->execute());
	}

	public function __call($name, $arguments)
	{
		$ret = call_user_func_array([$this->builder, $name], $arguments);
		if ($ret !== $this->builder) {
			return $ret;
		}
		return $this;
	}
}
