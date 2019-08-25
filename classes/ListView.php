<?php

namespace Nin;

class ListView
{
	public $id = '';
	public $provider = null;
	public $filter = null;
	public $page = 1;
	public $perpage = 0;
	public $renderPaging = true;

	public $total = 0;
	public $rendered = 0;

	public function __construct($provider)
	{
		$this->provider = $provider;
	}

	public function render($view, $options = array())
	{
		if($this->provider === null) {
			nf_error(11);
			return;
		}

		$this->total = -1;
		$this->rendered = 0;

		$this->provider->begin();
		while($obj = $this->provider->getNext()) {
			$filter = $this->filter;
			if($filter !== null && !$filter($obj)) {
				continue;
			}

			$this->total++;

			if($this->perpage != 0) {
				if($this->total < ($this->page - 1) * $this->perpage) {
					continue;
				}
				if($this->rendered >= $this->perpage) {
					break;
				}
			}

			$this->renderOne($view, $obj, $options);
			$this->rendered++;
		}
		$this->total = $this->provider->count();
		$this->provider->end();

		if($this->perpage != 0 && $this->renderPaging) {
			$this->renderPagingButtons();
		}
	}

	function renderOne($view, $item, $options = array())
	{
		global $nf_www_dir;
		global $nf_cfg;
		global $nf_module;
		global $nf_current_controllername;

		if (is_callable($view)) {
			$r = new \ReflectionFunction($view);
			$params = $r->getParameters();
			$args = array();
			foreach ($params as $param) {
				if ($param->getName() == 'item') {
					$args[] = $item;
				} else if (isset($options[$param->getName()])) {
					$args[] = $options[$param->getName()];
				} else if ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					nf_error(6, $param->getName());
					return;
				}
			}
			call_user_func_array($view, $args);
		} else {
			$inc_path = $nf_www_dir . '/' . $nf_cfg['paths']['views'];
			if($view[0] == '/') {
				$inc_path .= $view . '.php';
			} else {
				$inc_path .= $nf_module . $nf_current_controllername . '/' . $view . '.php';
			}

			extract($options);
			include($inc_path);
		}
	}

	function getPagingUrl($page)
	{
		$qs = $_SERVER['QUERY_STRING'];
		$qs = preg_replace('/(&?page' . $this->id . '=[0-9]+|page' . $this->id . '=[0-9]+&?)/', '', $qs);
		if($qs == '') {
			return '?page' . $this->id . '=' . $page;
		}
		return '?' . $qs . '&page' . $this->id . '=' . $page;
	}

	function renderPagingButtons()
	{
		$pages = ceil($this->total / $this->perpage);
		if($pages <= 1) {
			return;
		}
		echo '<div class="nf-pagebuttons">';
		if($this->page > 1) {
			echo '<a class="nf-pagebutton nf-previous" href="' . $this->getPagingUrl($this->page - 1) . '">&lt;</a>';
		}
		$separatedLeft = false;
		$separatedRight = false;
		echo '<ul class="nf-pagebutton-list">';
		for($i=1; $i<=$pages; $i++) {
			if(!$separatedLeft && $i > 4 && $i - $this->page <= -4) {
				echo '<li><span class="nf-pagebutton-separator">...</span></li>';
				$i = $this->page - 4;
				$separatedLeft = true;
				continue;
			}
			if(!$separatedRight && $i - $this->page >= 4) {
				echo '<li><span class="nf-pagebutton-separator">...</span></li>';
				$i = $pages - 4;
				$separatedRight = true;
				continue;
			}
			$classname = 'nf-pagebutton';
			if($i == $this->page) {
				$classname .= ' nf-current';
			}
			echo '<li><a class="' . $classname . '" href="' . $this->getPagingUrl($i) . '">' . $i . '</a></li>';
		}
		echo '</ul>';
		if($this->page < $pages) {
			echo '<a class="nf-pagebutton nf-next" href="' . $this->getPagingUrl($this->page + 1) . '">&gt;</a>';
		}
		echo '</div>';
	}
}
