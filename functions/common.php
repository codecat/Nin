<?php

function nf_xcopy($src, $dst, $ignore = [])
{
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ($file = readdir($dir))) {
		if($file != '.' && $file != '..') {
			if(is_dir($src . '/' . $file)) {
				nf_xcopy($src . '/' . $file, $dst . '/' . $file, $ignore);
			} elseif(array_search($src . '/' . $file, $ignore) === false) {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}
