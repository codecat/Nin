<?php

namespace Nin\Database;

abstract class Context
{
	public abstract function query($query);
	public abstract function beginBuild($table);
}
