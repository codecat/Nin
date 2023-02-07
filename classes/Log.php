<?php

namespace Nin;

class Log
{
	public static function check($fnm)
	{
		global $nf_project_dir;

		$logs_path = $nf_project_dir . DIRECTORY_SEPARATOR . 'logs';

		if(!file_exists($logs_path)) {
			mkdir($logs_path);
		}
		$path = $logs_path . DIRECTORY_SEPARATOR . $fnm;
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
