<?php

class Nin
{
	public static $user_class = 'User';
	public static $session_prefix = 'nin_';
	
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

	public static function timeAgo($oldTime, $tags = true)
	{
		$timeCalc = 0;
		if(preg_match("/^[0-9]+$/", $oldTime)) {
			$timeCalc = time() - intval($oldTime);
		} else {
			$timeCalc = time() - strtotime($oldTime);
		}
		$timeType = "s";
		if($timeCalc >= 60) {
			$timeType = "m";
		}
		if($timeCalc >= (60*60)) {
			$timeType = "h";
		}
		if($timeCalc >= (60*60*24)) {
			$timeType = "d";
		}
		if($timeType == "s") {
			if($timeCalc == 1) {
				$timeCalc .= " second ago";
			} else {
				$timeCalc .= " seconds ago";
			}
		}
		if($timeType == "m") {
			$mins = round($timeCalc/60);
			$timeCalc = $mins . " minute" . ($mins != 1 ? "s" : "") . " ago";
		}
		if($timeType == "h") {
			$hrs = round($timeCalc/60/60);
			$timeCalc = $hrs . " hour" . ($hrs != 1 ? "s" : "") . " ago";
		}
		if($timeType == "d") {
			$days = round($timeCalc/60/60/24);
			$timeCalc = $days . " day" . ($days != 1 ? "s" : "") . " ago";
		}
		if($tags) {
			return "<span title=\"" . $oldTime . "\">" . $timeCalc . "</span>";
		} else {
			return $timeCalc;
		}
	}
}
