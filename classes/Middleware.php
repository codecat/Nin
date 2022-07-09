<?php

namespace Nin;

abstract class Middleware
{
	/**
	 * @return bool Whether or not execution can continue.
	 */
	public abstract function exec(string $action) : string|false;
}
