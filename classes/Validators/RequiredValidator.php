<?php

namespace Nin\Validators;

use Nin\Validator;

class RequiredValidator extends Validator
{
	public function validate()
	{
		$allok = true;

		foreach($this->keys as $key) {
			if(empty($this->model->$key)) {
				$this->error .= nf_t('$key is required.', ['$key' => $key]) . "\n";
				$allok = false;
			}
		}
		return $allok;
	}
}
