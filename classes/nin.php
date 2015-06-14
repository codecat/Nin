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
	
	public static function setuid($uid)
	{
		Nin::setsession('user_id', $uid);
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
}
