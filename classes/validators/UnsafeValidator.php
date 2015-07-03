<?php

class UnsafeValidator extends Validator
{
	public function validate()
	{
		return false;
	}
}
