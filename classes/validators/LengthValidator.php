<?php

class LengthValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		foreach($this->keys as $key) {
			if(isset($this->arguments['min'])) {
				$min = intval($this->arguments['min']);
				if(strlen($this->model->$key) < $min) {
					$this->error .= "$key is shorter than $min characters\n";
					$allok = false;
				}
			}
			
			if(isset($this->arguments['max'])) {
				$max = intval($this->arguments['max']);
				if(strlen($this->model->$key) > $max) {
					$this->error .= "$key is longer than $max characters\n";
					$allok = false;
				}
			}
		}
		return $allok;
	}
}
