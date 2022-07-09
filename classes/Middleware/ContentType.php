<?php

namespace Nin\Middleware;

class ContentType extends \Nin\Middleware
{
	private string $mime;

	public function __construct(string $mime)
	{
		$this->mime = $mime;
	}

	public function exec(string $action) : string|false
	{
		header('Content-Type: ' . $this->mime);
		return $action;
	}
}
