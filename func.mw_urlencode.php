<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * php's urlencode just isnt enough for mediawiki.
 *  this function replaces spaces with underscores BEFORE urlencoding,
 *  preventing them from being turned into %20
 */

function mw_urlencode($s)
{
	return urlencode(str_replace(" ", "_", $s) );
}

function mw_urldecode($s)
{
	return str_replace("_", " ", urldecode($s) );
}

