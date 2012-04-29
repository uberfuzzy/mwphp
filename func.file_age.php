<?php
/*
	returns diff between reported file create time vs file mod time
*/

function file_age($fn)
{
	if( file_exists($fn) === false ) return false;
	$c = filectime($fn);
	$m = filemtime($fn);
	return $c - $m;
}
