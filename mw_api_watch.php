<?php
/*
* action=watch *
  Add or remove a page from/to the current user's watchlist

This module requires read rights.
This module requires write rights.
Parameters:
  title          - The page to (un)watch
  unwatch        - If set the page will be unwatched rather than watched
Examples:
  api.php?action=watch&title=Main_Page
  api.php?action=watch&title=Main_Page&unwatch
*/

require_once "mw_api_get.php";
function mw_api_watch($site, $title)
{
	$param = array(
		'action' => 'watch',
		'title' => $title,
		);

	// $ret = mw_api_post($site, $param, 1);
	$ret = mw_api_get($site, $param, 1);

	if( array_key_exists('error', $ret) )
	{
		global $LAST_WATCH_ERROR_CODE;
		$LAST_WATCH_ERROR_CODE = $ret['error']['code'];
		var_dump($ret);
		return false;
	}

	if( array_key_exists('watch', $ret) == false)
	{
		var_dump($ret);
		return false;
	}

	if( array_key_exists('watched', $ret['watch']) == false )
	{
		var_dump($ret);
		return false;
	}

	return true;
}

function mw_api_unwatch($site, $title)
{
	$param = array(
		'action' => 'watch',
		'title' => $title,
		'unwatch' => '1',
		);

	// $ret = mw_api_post($site, $param, 1);
	$ret = mw_api_get($site, $param, 1);

	if( !$ret )
	{
		global $LAST_GET_RAW;
		global $LAST_GET_CGI;
		// $LAST_GET_RAW = $LAST_GET_CGI = null;
		var_dump($LAST_GET_RAW);
		var_dump($LAST_GET_CGI);
		return false;
	}

	if( array_key_exists('error', $ret) )
	{
		global $LAST_WATCH_ERROR_CODE;
		$LAST_WATCH_ERROR_CODE = $ret['error']['code'];
		var_dump($ret);
		return false;
	}

	if( array_key_exists('watch', $ret) == false)
	{
		var_dump($ret);
		return false;
	}

	if( array_key_exists('unwatched', $ret['watch']) == false )
	{
		var_dump($ret);
		return false;
	}

	return true;
}