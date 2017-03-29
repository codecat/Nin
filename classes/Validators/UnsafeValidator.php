<?php

namespace Nin\Validators;

use Nin\Validator;

class UnsafeValidator extends Validator
{
	public function validate()
	{
		return false;
	}
}
