<?php

namespace Nin;

class Validator
{
	public $model = null;
	public $keys = array();
	public $value = '';
	public $arguments = array();

	public $error = '';

	public function validate()
	{
		return true;
	}
}
