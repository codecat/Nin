<?php

namespace Nin\Database;

abstract class Result
{
	public abstract function fetch_assoc();
	public abstract function insert_id();
	public abstract function num_rows();
}
