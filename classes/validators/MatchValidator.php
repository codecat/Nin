<?php

class MatchValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		foreach($this->keys as $key) {
			$result = preg_match($this->value, $this->model->$key);
			if($result === false) {
				throw new Exception('Wrong regex pattern for key ' . $key);
			} elseif($result === 0) {
				$allok = false;
				$this->error .= "$key does not match pattern.\n";
			}
		}
		return $allok;
	}
}
