<?php

namespace Nin;

class Controller
{
	public $layout = 'views/layout.php';
	public $title = '';
	public $files_css = [];
	public $files_js = [];
	public $views_folder = [];

	public function beforeAction($action)
	{
		return $action;
	}

	public function displayError($error, $code = 500)
	{
		header('HTTP/1.1 ' . $code);
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

	public function getTitle()
	{
		global $nf_cfg;
		$name = Html::encode($nf_cfg['name']);
		if ($this->title != '') {
			return Html::encode($this->title) . ' - ' . $name;
		}
		return $name;
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
			$title = $this->getTitle();
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

		//NOTE: I would've put this in the constructor, but sites rely on not calling the
		//      parent constructor, so this is much easier.
		if (count($this->views_folder) == 0) {
			$this->views_folder[] = strtolower(substr(get_class($this), 0, -strlen('Controller')));
		}

		$inc_folder = $this->views_folder[count($this->views_folder) - 1];
		$inc_folder_pushed = false;

		$view_path = '';

		$inc_path = $nf_www_dir . '/' . $nf_cfg['paths']['views'];
		if($view[0] == '/') {
			$view_path = $view;
		} else {
			$view_path = '/' . $inc_folder . '/' . $view;
		}

		// Push the new folder as the current views folder, so that views can use their
		// relative path to render partial views easier.
		//
		// For example:
		//   '/foo/view'
		//     ^^^
		//   '/foo/bar/view'
		//     ^^^^^^^
		$m = [];
		if (preg_match('/^\/?(.*)\/.*$/', $view, $m) && strlen($m[1]) > 0) {
			array_push($this->views_folder, $m[1]);
			$inc_folder_pushed = true;
		}

		$inc_path .= $view_path;

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

		if ($inc_folder_pushed) {
			array_pop($this->views_folder);
		}

		return ob_get_clean();
	}

	public function redirect($url, $code = 302)
	{
		header('HTTP/1.1 ' . $code);
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
