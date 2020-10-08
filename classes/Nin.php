<?php

namespace Nin;

class Nin
{
	public static $user_class = 'User';
	public static $session_prefix = 'nin_';
	public static $date_format = 'M jS';
	public static $date_format_year = ', Y';

	public static $current_user = null;

	public static function randomString($n, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		$len = strlen($characters);
		$ret = '';
		for($i=0; $i<$n; $i++) {
			$ret .= $characters[rand(0, $len-1)];
		}
		return $ret;
	}

	public static function getSession($key)
	{
		$arrkey = Nin::$session_prefix . $key;
		if(isset($_SESSION[$arrkey])) {
			return $_SESSION[$arrkey];
		}
		return false;
	}

	public static function setSession($key, $value)
	{
		$_SESSION[Nin::$session_prefix . $key] = $value;
	}

	public static function unsetSession($key)
	{
		unset($_SESSION[Nin::$session_prefix . $key]);
	}

	public static function setuid($uid)
	{
		Nin::setSession('user_id', $uid);
	}

	public static function unsetuid()
	{
		Nin::unsetSession('user_id');
	}

	public static function uid()
	{
		return Nin::getSession('user_id');
	}

	public static function user()
	{
		if(Nin::$current_user !== null) {
			return Nin::$current_user;
		}
		$uid = Nin::uid();
		if($uid === false) {
			return false;
		}
		$classname = Nin::$user_class;
		$user = $classname::findByPk($uid);
		Nin::$current_user = $user;
		return $user;
	}

	public static function setlanguage($language)
	{
		global $nf_cfg;

		foreach($nf_cfg['i18n']['languages'] as $lang) {
			if($lang == $language) {
				Nin::setSession('language', $language);
				return true;
			}
		}

		return false;
	}

	public static function language()
	{
		$ret = Nin::getSession('language');
		if($ret !== false) {
			return $ret;
		}

		global $nf_cfg;
		return $nf_cfg['i18n']['language'];
	}

	public static function timeFormat($epoch)
	{
		$ret = date(Nin::$date_format, intval($epoch));
		if(date('Y') != date('Y', $epoch) && Nin::$date_format_year != '') {
			return $ret .= date(Nin::$date_format_year, $epoch);
		}
		return $ret;
	}

	public static function relativeTime($time, $tags = true, $uppercase = false)
	{
		if(preg_match("/^[0-9]+$/", $time)) {
			$time = intval($time);
		} elseif (!is_int($time)) {
			$time = strtotime($time);
		}

		$ret = '';
		$timeCalc = abs(time() - $time);
		if ($timeCalc >= (60*60*24)) {
			$days = round($timeCalc/60/60/24);
			$ret = Nin::multiple($days, nf_t('day'), nf_t('days'));
		} elseif ($timeCalc >= (60*60)) {
			$hrs = round($timeCalc/60/60);
			$ret = Nin::multiple($hrs, nf_t('hour'), nf_t('hours'));
		} elseif ($timeCalc >= 60) {
			$mins = round($timeCalc/60);
			$ret = Nin::multiple($mins, nf_t('minute'), nf_t('minutes'));
		} else {
			$ret = Nin::multiple($timeCalc, nf_t('second'), nf_t('seconds'));
		}

		if ($time <= time()) {
			$ret .= ' ' . nf_t('ago');
		} else {
			$ret = nf_t('in') . ' ' . $ret;
		}

		if ($uppercase) {
			$ret = ucfirst($ret);
		}

		if ($tags) {
			return "<span title=\"" . Nin::timeFormat($time) . "\">" . $ret . "</span>";
		}
		return $ret;
	}

	public static function multiple($count, $verb, $verbs, $verbonly = false)
	{
		if($count == 1) {
			if($verbonly) {
				return $verb;
			}
			return $count . ' ' . $verb;
		}
		if($verbonly) {
			return $verbs;
		}
		return $count . ' ' . $verbs;
	}
}
