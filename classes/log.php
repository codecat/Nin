<?php

class Log
{
	public static function check($fnm)
	{
		global $nf_www_dir;
		if(!file_exists($nf_www_dir . '/logs')) {
			mkdir($nf_www_dir . '/logs');
		}
		$path = $nf_www_dir . '/logs/' . $fnm;
		if(!file_exists($path)) {
			touch($path);
		}
		return $path;
	}

	public static function write($key, $str)
	{
		$path = Log::check(date('Y-m-d') . '.log');
		$fp = fopen($path, 'a');
		fwrite($fp, '[' . date('Y-m-d H:i:s') . '][' . strtoupper($key) . '] ' . $str . "\n");
		fclose($fp);
	}

	public static function info($str)
	{
		Log::write('info', $str);
	}

	public static function error($str)
	{
		Log::write('error', $str);
	}

	public static function warning($str)
	{
		Log::write('warning', $str);
	}
}
