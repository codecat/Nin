<?php

namespace Nin\Renderers;

use Nin\Renderer;

class PhpRenderer extends Renderer
{
	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
	}

	public function render($inc_path, $options)
	{
		$func = function($inc_path, $options) {
			extract($options);
			include($inc_path);
		};
		$func->bindTo($this->controller, $this->controller);
		$func($inc_path, $options);
	}
}
