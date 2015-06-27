<?php

class Controller
{
	public $layout = 'views/layout.php';

	public function beforeAction($action)
	{
		return $action;
	}
	
	public function render($view, $options = array())
	{
		global $nf_www_dir;
		$content = $this->renderPartial($view, $options);
		include($nf_www_dir . '/' . $this->layout);
	}
	
	public function renderPartial($view, $options = array())
	{
		extract($options);
		
		global $nf_www_dir;
		global $nf_cfg;
		
		$inc_folder = strtolower(substr(get_class($this), 0, -strlen(__CLASS__)));
		$inc_path = $nf_www_dir . '/' . $nf_cfg['paths']['views'];
		if($view[0] == '/') {
			$inc_path .= $view . '.php';
		} else {
			$inc_path .= '/' . $inc_folder . '/' . $view . '.php';
		}
		
		ob_start();
		include($inc_path);
		return ob_get_clean();
	}
	
	public function redirect($url)
	{
		header('Location: ' . $url);
		exit;
	}
}
