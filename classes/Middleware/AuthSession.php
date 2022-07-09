<?php

namespace Nin\Middleware;

class AuthSession extends \Nin\Middleware
{
	private string|false $redirect;
	private string|false $return_param;

	public function __construct(string|false $redirect = '/login', string|false $return_param = 'r')
	{
		$this->redirect = $redirect;
		$this->return_param = $return_param;
	}

	public function exec(string $action) : string|false
	{
		if (\Nin\Nin::uid() === false) {
			if ($this->redirect === false) {
				return false;
			}

			$location = $this->redirect;
			if ($this->return_param !== false) {
				if (str_contains($location, '?')) {
					$location .= '&';
				} else {
					$location .= '?';
				}
				$location .= $this->return_param . '=' . urlencode($_SERVER['REQUEST_URI']);
			}
			header('Location: ' . $location);
			return false;
		}

		return $action;
	}
}
