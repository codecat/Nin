<?php

class RequiredValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		
		foreach($this->keys as $key) {
			if(empty($this->model->$key)) {
				$this->error .= "$key is required\n";
				$allok = false;
			}
		}
		return true;
	}
}
