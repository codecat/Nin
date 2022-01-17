<?php

namespace Nin\ListViews;

use Nin\ListView;
use Nin\Controller;
use Nin\Database\ModelQueryBuilder;

class ModelListView extends ListView
{
	protected $builder;

	public function __construct(Controller $controller, int $page, ModelQueryBuilder $builder)
	{
		parent::__construct($page, $controller);

		$this->builder = $builder;

		$this->builder->count();
		$this->total = $this->builder->executeCount();

		$this->builder->select();
	}

	function renderItems($view, $options = [])
	{
		if ($this->perpage != 0) {
			$this->builder->page($this->page - 1, $this->perpage);
		}

		$items = $this->builder->findAll();

		foreach ($items as $item) {
			echo $this->controller->renderPartial($view, array_merge(
				$options, [ 'item' => $item ]
			));
		}
	}
}
