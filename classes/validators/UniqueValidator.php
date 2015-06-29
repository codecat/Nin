<?php

class UniqueValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		foreach($this->keys as $key) {
			$value = $this->model->$key;
			$count = $this->model->countByAttributes(array($key => $value));
			if($count > 0) {
				$allok = false;
				$this->error .= "$key must be unique.\n";
			}
		}
		return $allok;
	}
}
