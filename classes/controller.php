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
		global $nf_cfg_path_views;
		extract($options);
		$folder = strtolower(substr(get_class($this), 0, -strlen(__CLASS__)));
		ob_start();
		include($nf_www_dir . '/' . $nf_cfg_path_views . '/' . $folder . '/' . $view . '.php');
		return ob_get_clean();
	}
	
	function findAll($modelname)
	{
		$tablename = $modelname::tableName();
		$res = nf_query('SELECT * FROM `' . $tablename . '`');
		print_r($res);
	}
}
