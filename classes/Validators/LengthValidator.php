<?php

namespace Nin\Validators;

use Nin\Validator;

class LengthValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		foreach($this->keys as $key) {
			if(isset($this->arguments['min'])) {
				$min = intval($this->arguments['min']);
				if(strlen($this->model->$key) < $min) {
					$this->error .= nf_t('$key is shorter than $min characters.', ['$key' => $key, '$min' => $min]) . "\n";
					$allok = false;
				}
			}

			if(isset($this->arguments['max'])) {
				$max = intval($this->arguments['max']);
				if(strlen($this->model->$key) > $max) {
					$this->error .= nf_t('$key is longer than $max characters.', ['$key' => $key, '$max' => $max]) . "\n";
					$allok = false;
				}
			}
		}
		return $allok;
	}
}
