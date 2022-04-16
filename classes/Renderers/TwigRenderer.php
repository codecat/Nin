<?php

namespace Nin\Renderers;

use Nin\Renderer;

class TwigRenderer extends Renderer
{
	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
	}

	public function render($inc_path, $options)
	{
		global $nf_cfg;

		$loader = new \Twig\Loader\FilesystemLoader(dirname($inc_path));
		$twig = new \Twig\Environment($loader, [
			'cache' => $nf_cfg['render']['cache'],
			'debug' => $nf_cfg['render']['debug'],
		]);
		$twig->display(basename($inc_path), $options);
	}
}
