<?php

namespace Nin\Database\QueryBuilders;

use Nin\Database\QueryBuilder;

class MySQL extends QueryBuilder
{
	private function buildWhere()
	{
		if(count($this->where) == 0) {
			return '';
		}

		$ret = ' WHERE';

		for($i = 0; $i < count($this->where); $i++) {
			$where = $this->where[$i];

			$key = $where[0];
			$value = $where[1];
			$oper = $where[2];

			if($key == '' || $oper == '') {
				continue;
			}

			if($i > 0) {
				$ret .= ' AND';
			}
			$ret .= ' \'' . $key . '\'=' . nf_sql_encode($value);
		}

		return $ret;
	}

	private function buildSelect()
	{
		$query = 'SELECT * FROM ' . $this->table;
		$query .= $this->buildWhere();
		return $query . ';';
	}

	private function buildUpdate()
	{
		$query = 'UPDATE ' . $this->table . ' SET';
		for($i = 0; $i < count($this->set); $i++) {
			$set = $this->set[$i];
			if($i > 0) {
				$query .= ',';
			}
			$query .= ' \'' . $set[0] . '\'=' . nf_sql_encode($set[1]);
		}
		$query .= $this->buildWhere();
		return $query;
	}

	public function build()
	{
		if($this->method == '') {
			return '';
		}

		if($this->method == 'SELECT') {
			return $this->buildSelect();
		} else if ($this->method == 'UPDATE' && count($this->set) > 0) {
			return $this->buildUpdate();
		} else if ($this->method == 'INSERT') {
			//TODO
		}

		return '';
	}
}
