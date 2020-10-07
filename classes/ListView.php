<?php

namespace Nin;

class ListView
{
	public $id = '';
	public $provider = null;
	public $controller = null;
	public $filter = null;
	public $page = 1;
	public $perpage = 0;
	public $renderPaging = true;

	public $total = 0;
	public $rendered = 0;

	public function __construct($provider, $controller = null)
	{
		$this->provider = $provider;
		$this->controller = $controller;
	}

	public function render($view, $options = array())
	{
		$this->renderItems($view, $options);
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

	function renderItems($view, $options = array())
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
	}

	function renderPagingButtons()
	{
		$showAround = 4;

		if ($this->perpage == 0) {
			return;
		}

		$numPages = ceil($this->total / $this->perpage);
		if ($numPages <= 1) {
			return;
		}

		$index = $this->page - 1;
		$start = $index - $showAround;
		$end = $index + $showAround;

		$pages = array(0);
		for ($i = $start; $i <= $end; $i++) {
			if ($i == $start && $i > 1) {
				$pages[] = '...';
			}
			if ($i > 0 && $i < $numPages - 1) {
				$pages[] = $i;
			}
			if ($i == $end && $i < $numPages - 2) {
				$pages[] = '...';
			}
		}
		if ($numPages > 1) {
			$pages[] = $numPages - 1;
		}

		echo '<div class="nf-pagebuttons">';
		if ($index > 0) {
			echo '<a class="nf-pagebutton nf-previous" href="' . $this->getPagingUrl($this->page - 1) . '">&lt;</a>';
		}
		echo '<ul class="nf-pagebutton-list">';
		foreach ($pages as $i) {
			if ($i === '...') {
				echo '<li><span class="nf-pagebutton-separator">...</span></li>';
			} else {
				$classname = 'nf-pagebutton';
				if($i == $index) {
					$classname .= ' nf-current';
				}
				echo '<li><a class="' . $classname . '" href="' . $this->getPagingUrl($i + 1) . '">' . ($i + 1) . '</a></li>';
			}
		}
		echo '</ul>';
		if ($index < $numPages - 1) {
			echo '<a class="nf-pagebutton nf-next" href="' . $this->getPagingUrl($this->page + 1) . '">&gt;</a>';
		}
		echo '</div>';
	}
}
