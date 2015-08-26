<?php

class RequiredValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		
		foreach($this->keys as $key) {
			if(empty($this->model->$key)) {
				$this->error .= nf_t('$key is required.', array('$key' => $key)) . "\n";
				$allok = false;
			}
		}
		return $allok;
	}
}
