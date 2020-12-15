<?php

namespace Nin\Database\QueryBuilders;

use Nin\Database\QueryBuilder;

class MySQL extends QueryBuilder
{
	private function encode($o)
	{
		if(is_string($o)) {
			return "'" . $this->context->real_escape_string($o) . "'";
		} elseif(is_float($o)) {
			return str_replace(',', '.', strval(floatval($o)));
		} elseif(is_numeric($o)) {
			return intval($o);
		}

		return $o;
	}

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
			$ret .= ' `' . $key . '`' . $oper . $this->encode($value);
		}

		return $ret;
	}

	private function buildSelect()
	{
		$query = 'SELECT ';
		if(count($this->get) == 0) {
			$query .= '*';
		} else {
			$query .= implode(',', $this->get);
		}
		$query .= ' FROM ' . $this->table;
		$query .= $this->buildWhere();
		if($this->group != '') {
			$query .= ' GROUP BY `' . $this->group . '`';
		}
		for($i = 0; $i < count($this->orderby); $i++) {
			if($i == 0) {
				$query .= ' ORDER BY ';
			} else {
				$query .= ',';
			}
			$order = $this->orderby[$i];
			$query .= '`' . $order[0] . '`';
			if(strcasecmp($order[1], 'DESC') == 0) {
				$query .= ' DESC';
			}
		}
		if($this->limit[0] >= 0 && $this->limit[1] >= 0) {
			$query .= ' LIMIT ' . $this->limit[0] . ',' . $this->limit[1];
		}
		return $query . ';';
	}

	private function buildUpdate()
	{
		if(count($this->set) == 0) {
			return '';
		}
		$query = 'UPDATE ' . $this->table . ' SET';
		$count = 0;
		for($i = 0; $i < count($this->set); $i++) {
			$set = $this->set[$i];
			if($set[1] === null) {
				continue;
			}
			if($count > 0) {
				$query .= ',';
			}
			$query .= ' `' . $set[0] . '`=' . $this->encode($set[1]);
			$count++;
		}
		$query .= $this->buildWhere();
		return $query . ';';
	}

	private function buildInsert()
	{
		$numRows = count($this->insertValues);
		if($numRows == 0) {
			return '';
		}
		$query = 'INSERT INTO ' . $this->table . ' (';
		$numCols = 0;
		for($i = 0; $i < $numRows; $i++) {
			$row = $this->insertValues[$i];
			if($numCols == 0 && $i == 0) {
				$keys = array_keys($row);
				$numCols = count($keys);
				for($j = 0; $j < $numCols; $j++) {
					if($j > 0) {
						$query .= ',';
					}
					$query .= $keys[$j];
				}
				$query .= ') VALUES ';
			}
			if($i > 0) {
				$query .= ',';
			}
			$query .= '(';
			$vals = array_values($row);
			for($j = 0; $j < $numCols; $j++) {
				if($j > 0) {
					$query .= ',';
				}
				$query .= $this->encode($vals[$j]);
			}
			$query .= ')';
		}
		return $query . ';';
	}

	private function buildDelete()
	{
		$query = 'DELETE FROM ' . $this->table;
		$query .= $this->buildWhere();
		return $query . ';';
	}

	private function buildCount()
	{
		$query = 'SELECT COUNT(*) AS c FROM ' . $this->table;
		$query .= $this->buildWhere();
		return $query . ';';
	}

	private function buildFindPK()
	{
		return 'SHOW KEYS FROM ' . $this->table . ' WHERE Key_name = \'PRIMARY\';';
	}

	public function build()
	{
		if($this->method == '') {
			return '';
		}

		if($this->method == 'SELECT') {
			return $this->buildSelect();
		} elseif($this->method == 'UPDATE') {
			return $this->buildUpdate();
		} elseif($this->method == 'INSERT') {
			return $this->buildInsert();
		} elseif($this->method == 'DELETE') {
			return $this->buildDelete();
		} elseif($this->method == 'COUNT') {
			return $this->buildCount();
		} elseif($this->method == 'FINDPK') {
			return $this->buildFindPK();
		}

		return '';
	}
}
