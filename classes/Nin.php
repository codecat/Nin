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

	public static function getsession($key)
	{
		$arrkey = Nin::$session_prefix . $key;
		if(isset($_SESSION[$arrkey])) {
			return $_SESSION[$arrkey];
		}
		return false;
	}

	public static function setsession($key, $value)
	{
		$_SESSION[Nin::$session_prefix . $key] = $value;
	}

	public static function unsetsession($key)
	{
		unset($_SESSION[Nin::$session_prefix . $key]);
	}

	public static function setuid($uid)
	{
		Nin::setsession('user_id', $uid);
	}

	public static function unsetuid()
	{
		Nin::unsetsession('user_id');
	}

	public static function uid()
	{
		return Nin::getsession('user_id');
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
				Nin::setsession('language', $language);
				return true;
			}
		}

		return false;
	}

	public static function language()
	{
		$ret = Nin::getsession('language');
		if($ret !== false) {
			return $ret;
		}

		global $nf_cfg;
		return $nf_cfg['i18n']['language'];
	}

	public static function timeFormat($epoch)
	{
		$ret = date(Nin::$date_format, intval($epoch));
		if(date('Y') != date('Y', $epoch)) {
			return $ret .= date(Nin::$date_format_year, $epoch);
		}
		return $ret;
	}

	public static function timeAgo($oldTime, $tags = true)
	{
		$timeCalc = 0;
		$strOldTime = $oldTime;
		if(preg_match("/^[0-9]+$/", $oldTime)) {
			$timeCalc = time() - intval($oldTime);
			$strOldTime = Nin::timeFormat($oldTime);
		} else {
			$timeCalc = time() - strtotime($oldTime);
		}

		$timeType = 's';
		if($timeCalc >= 60) {
			$timeType = 'm';
		}
		if($timeCalc >= (60*60)) {
			$timeType = 'h';
		}
		if($timeCalc >= (60*60*24)) {
			$timeType = 'd';
		}

		if($timeType == "s") {
			$timeCalc = Nin::multiple($timeCalc, nf_t('second'), nf_t('seconds')) . ' ' . nf_t('ago');
		}
		if($timeType == "m") {
			$mins = round($timeCalc/60);
			$timeCalc = Nin::multiple($mins, nf_t('minute'), nf_t('minutes')) . ' ' . nf_t('ago');
		}
		if($timeType == "h") {
			$hrs = round($timeCalc/60/60);
			$timeCalc = Nin::multiple($hrs, nf_t('hour'), nf_t('hours')) . ' ' . nf_t('ago');
		}
		if($timeType == "d") {
			$days = round($timeCalc/60/60/24);
			$timeCalc = Nin::multiple($days, nf_t('day'), nf_t('days')) . ' ' . nf_t('ago');
		}
		if($tags) {
			return "<span title=\"" . $strOldTime . "\">" . $timeCalc . "</span>";
		} else {
			return $timeCalc;
		}
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
