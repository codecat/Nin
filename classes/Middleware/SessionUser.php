<?php

namespace Nin\Middleware;

class SessionUser extends \Nin\Middleware
{
	private string|bool $redirect;

	public function __construct(string|false $redirect = '/login')
	{
		$this->redirect = $redirect;
	}

	public function exec(string $action) : string|false
	{
		if (\Nin\Nin::uid() === false) {
			if ($this->redirect !== false) {
				header('Location: ' . $this->redirect);
			}
			return false;
		}
		return $action;
	}
}
