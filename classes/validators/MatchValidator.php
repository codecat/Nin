<?php

class MatchValidator extends Validator
{
	public function validate()
	{
		$allok = true;
		foreach($this->keys as $key) {
			$v = $this->model->$key;
			if(empty($v)) {
				continue;
			}
			$result = preg_match($this->value, $v);
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
