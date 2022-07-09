<?php

namespace Nin;

abstract class ListView
{
	public $id = '';
	public $controller = null;
	public $filter = null;
	public $page = 1;
	public $perpage = 25;
	public $renderPaging = true;

	public $total = 0;
	public $rendered = 0;

	public function __construct(int $page, Controller $controller = null)
	{
		$this->controller = $controller;
		$this->page = $page;
	}

	public function render($view, $options = [])
	{
		$this->renderItems($view, $options);
		if($this->perpage != 0 && $this->renderPaging) {
			$this->renderPagingButtons();
		}
	}

	protected function renderOne($view, $item, $options = [])
	{
		global $nf_www_dir;

		if (is_callable($view)) {
			$r = new \ReflectionFunction($view);
			$params = $r->getParameters();
			$args = [];
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
			if ($this->controller) {
				echo $this->controller->renderPartial($view, array_merge($options, [
					'item' => $item,
				]));
			} else {
				$inc_path = $nf_www_dir . '/views/' . $view . '.php';

				extract($options);
				include($inc_path);
			}
		}
	}

	private function getPagingUrl($page)
	{
		$qs = $_SERVER['QUERY_STRING'];
		$qs = preg_replace('/(&?page' . $this->id . '=[0-9]+|page' . $this->id . '=[0-9]+&?)/', '', $qs);
		if($qs == '') {
			return '?page' . $this->id . '=' . $page;
		}
		return '?' . $qs . '&page' . $this->id . '=' . $page;
	}

	public function renderPagingButtons()
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

		$pages = [0];
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

	public abstract function renderItems($view, $options = []);
}
