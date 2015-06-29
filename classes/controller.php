<?php

class Controller
{
	public $layout = 'views/layout.php';
	public $files_css = array();
	public $files_js = array();

	public function beforeAction($action)
	{
		return $action;
	}

	public function displayError($error)
	{
		$this->render('/error', array('error' => $error));
	}

	public function registerCSS($filename)
	{
		$this->files_css[] = $filename;
	}

	public function registerJS($filename)
	{
		$this->files_js[] = $filename;
	}

	public function getHead()
	{
		$ret = '';
		foreach($this->files_css as $filename) {
			$ret .= "<link rel=\"stylesheet\" href=\"" . $filename . "\">\n";
		}
		foreach($this->files_js as $filename) {
			$ret .= "<script src=\"" . $filename . "\"></script>\n";
		}
		return $ret;
	}
	
	public function render($view, $options = array())
	{
		global $nf_www_dir;
		$content = $this->renderPartial($view, $options);
		$head = $this->getHead();
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
