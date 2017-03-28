<?php

namespace Nin\Validators;

use Nin\Validator;

class SafeValidator extends Validator
{
	public function validate()
	{
		return true;
	}
}
