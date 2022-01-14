<?php

namespace Nin;

class Validator
{
	public $model = null;
	public $keys = [];
	public $value = '';
	public $arguments = [];

	public $error = '';

	public function validate()
	{
		return true;
	}
}
