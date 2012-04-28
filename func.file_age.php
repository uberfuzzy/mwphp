<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

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
