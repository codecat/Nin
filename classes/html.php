<?php

class Html
{
	public static function encode($str)
	{
		return htmlentities($str, ENT_QUOTES, 'UTF-8');
	}

	private static function maketag($tagname, $void = true, $options = array())
	{
		$ret = '<' . $tagname;
		foreach($options as $k => $v) {
			if($v == '') {
				continue;
			}
			if(is_int($k)) {
				$ret .= ' ' . $v;
			} else {
				$ret .= ' ' . $k . '="' . Html::encode($v) . '"';
			}
		}
		if($void) {
			$ret .= '>';
		} else {
			$ret .= ' />';
		}
		return $ret;
	}

	public static function activeText($model, $key, $options = array())
	{
		return Html::maketag('input', true, array_merge(array(
			'type' => 'text',
			'name' => get_class($model) . '[' . $key . ']',
			'value' => $model->$key
		), $options));
	}

	public static function activeRadio($model, $key, $values = array(), $options = array())
	{
		$ret = '';
		foreach($values as $k => $v) {
			$checked = '';
			if($model->$key !== false && $model->$key == $k) {
				$checked = 'checked';
			}
			$ret .= '<label>' . Html::maketag('input', true, array_merge(array(
				'type' => 'radio',
				'name' => get_class($model) . '[' . $key . ']',
				'value' => $k,
				$checked
			), $options)) . ' ' . $v . '</label>' . "\n";
		}
		return $ret;
	}

	public static function activeDate($model, $key, $format, $options = array())
	{
		$date = '';
		if($model->$key) {
			$date = date($format, strtotime($model->$key));
		}
		return Html::maketag('input', true, array_merge(array(
			'type' => 'text',
			'name' => get_class($model) . '[' . $key . ']',
			'value' => $date
		), $options));
	}

	public static function activeCombo($model, $key, $values = array(), $options = array())
	{
		$ret = Html::maketag('select', true, array_merge(array(
			'name' => get_class($model) . '[' . $key . ']'
		), $options));
		foreach($values as $k => $v) {
			$selected = '';
			if(is_int($k)) {
				if($model->$key == $v) {
					$selected = ' selected';
				}
				$ret .= '<option value="' . $v . '"' . $selected . '>' . $v . '</option>';
			} else {
				if($model->$key == $k) {
					$selected = ' selected';
				}
				$ret .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
			}
		}
		$ret .= '</select>';
		return $ret;
	}

	public static function activeCheck($model, $key, $options = array(), $on = '1')
	{
		$checked = '';
		if($model->$key == $on) {
			$checked = 'checked';
		}
		return Html::maketag('input', true, array_merge(array(
			'type' => 'checkbox',
			'name' => get_class($model) . '[' . $key . ']',
			'value' => $on,
			$checked
		), $options));
	}
}
