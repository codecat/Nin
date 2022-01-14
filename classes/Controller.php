<?php

namespace Nin;

class Controller
{
	public $layout = 'views/layout.php';
	public $uri_parts = [];
	public $files_css = [];
	public $files_js = [];

	public function beforeAction($action)
	{
		return $action;
	}

	public function displayError($error)
	{
		$this->render('/error', ['error' => $error]);
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

	public function render($view, $options = [])
	{
		global $nf_www_dir;
		$content = $this->renderPartial($view, $options);
		$fnmlayout = $nf_www_dir . '/' . $this->layout;
		if(file_exists($fnmlayout)) {
			$head = $this->getHead();
			include($fnmlayout);
		} else {
			echo $content;
		}
	}

	public function renderPartial($view, $options = [])
	{
		global $nf_www_dir;
		global $nf_cfg;
		global $nf_module;

		$inc_folder = strtolower(substr(get_class($this), 0, -strlen('Controller')));
		$inc_path = $nf_www_dir . '/' . $nf_cfg['paths']['views'];
		if($view[0] == '/') {
			$inc_path .= $view;
		} else {
			$inc_path .= $nf_module . $inc_folder . '/' . $view;
		}

		$basename = basename($inc_path);
		if (strpos($basename, '.') === false) {
			$inc_path .= '.php';
		}

		$renderer = null;

		$ext = substr($inc_path, strrpos($inc_path, '.'));
		if ($ext == '.php') {
			$renderer = new \Nin\Renderers\PhpRenderer($this);
		} else {
			$renderer = nf_hook_one('viewrenderer', [$this, $inc_path, $ext]);
		}

		if ($renderer === null) {
			nf_error(15, $ext);
			return '';
		}

		ob_start();
		$renderer->render($inc_path, $options);
		return ob_get_clean();
	}

	public function redirect($url)
	{
		header('Location: ' . $url);
		exit;
	}

	public function cache($etag)
	{
		header('Etag: ' . $etag);
		header('Cache-Control: public');

		if (!isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
			return;
		}

		if (trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}
}
