<?php

namespace Nin\ListViews;

use Nin\ListView;

class ProviderListView extends \Nin\ListView
{
	protected $provider;

	public function __construct(\Nin\Controller $controller, int $page, \Nin\Provider $provider)
	{
		parent::__construct($page, $controller);
		$this->provider = $provider;
	}

	function renderItems($view, $options = [])
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
}
