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
			//TODO: This comma to period should be autodetected based on mysql server locale (how?)
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
			$ret .= ' `' . $key . '`=' . $this->encode($value);
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
		if(count($this->set) == 0) {
			return '';
		}
		$query = 'UPDATE ' . $this->table . ' SET';
		for($i = 0; $i < count($this->set); $i++) {
			$set = $this->set[$i];
			if($i > 0) {
				$query .= ',';
			}
			$query .= ' \'' . $set[0] . '\'=' . $this->encode($set[1]);
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