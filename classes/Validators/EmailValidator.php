<?php

namespace Nin\Validators;

use Nin\Validator;

class EmailValidator extends MatchValidator
{
	public function validate()
	{
		// Pattern taken from Yii's CEmailValidator (which in turn comes from regular-expressions.info/email.html)
		$this->value = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
		return parent::validate();
	}
}
