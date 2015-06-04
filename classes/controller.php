<?php

class Controller
{
	public $layout = 'views/layout.php';
	
	function render($view, $options = array())
	{
		global $nf_www_dir;
		$content = $this->renderPartial($view, $options);
		include($nf_www_dir . '/' . $this->layout);
	}
	
	function renderPartial($view, $options = array())
	{
		global $nf_www_dir;
		global $nf_cfg;
		
		extract($options);
		$folder = strtolower(substr(get_class($this), 0, -strlen(__CLASS__)));
		ob_start();
		include($nf_www_dir . '/' . $nf_cfg['paths']['views'] . '/' . $folder . '/' . $view . '.php');
		return ob_get_clean();
	}
}
