<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
* action=move *

This module requires read rights.
This module requires write rights.
This module only accepts POST requests.
Parameters:
  from           - Title of the page you want to move. Cannot be used together with fromid.
  fromid         - Page ID of the page you want to move. Cannot be used together with from.
  to             - Title you want to rename the page to.
  token          - A move token previously retrieved through prop=info
  reason         - Reason for the move (optional).
  movetalk       - Move the talk page, if it exists.
  movesubpages   - Move subpages, if applicable
  noredirect     - Don't create a redirect
  watch          - Add the page and the redirect to your watchlist
  unwatch        - Remove the page and the redirect from your watchlist
  ignorewarnings - Ignore any warnings
*/
require_once "mw_api_token.php";
require_once "mw_api_post.php";

/**
pass noredirect or movetalk in an array in 5th param
**/

function mw_api_move($site, $old_name, $new_name, $summary='', $options=null)
{
	global $LAST_MOVE_ERROR; 
	global $LAST_MOVE_ERROR_CODE;
	$LAST_MOVE_ERROR = null;
	$LAST_MOVE_ERROR_CODE = null;

	/**************************************************/
	$param = array();
	$param['action'] = 'move';

	$param['from'] = $old_name;
	$param['to'] = $new_name;
	
	$token = mw_api_get_token($site, $old_name, 'move');
	$param['token'] = $token;
	
	$param['reason'] = $summary;
	
	/* -------------------------------------------------- */
	
	if( is_array($options) )
	{
		//print "found options!\n";
		//print_r($options);
		
		if(
			array_search('movetalk', $options) !== false ||
			array_search('mt', $options) !== false
		)
		{
			//print "found movetalk\n";
			$param['movetalk'] = 1;
		}

		if(
			array_search('movesubpages', $options) !== false ||
			array_search('movesub', $options) !== false ||
			array_search('ms', $options) !== false
		)
		{
			//print "found movesubpages\n";
			$param['movesubpages'] = 1;
		}

		if(
			array_search('noredirect', $options) !== false ||
			array_search('nr', $options) !== false
		)
		{
			//print "found no redirect\n";
			$param['noredirect'] = 1;
		}

		if(
			array_search('fromid', $options) !== false ||
			array_search('fid', $options) !== false
			)
		{
			//print "found no redirect\n";
			$param['fromid'] = $old_name;
			unset($param['from']);
		}
	}

	/**************************************************/
	#global $LAST_MOVE_PARAM;
	#$LAST_MOVE_PARAM = $param;
	$ret = mw_api_post($site, $param);
	if($ret == false)
	{
		$LAST_MOVE_ERROR = "false from post";
		$LAST_MOVE_ERROR_CODE = 'fzy_post_false';
		return false;
	}

	if( array_key_exists('error', $ret) !== false)
	{
		//found error in package
		$LAST_MOVE_ERROR = "got error from post [{$ret['error']['code']}]";
		$LAST_MOVE_ERROR_CODE = $ret['error']['code'];
		return false;
	}

	if( array_key_exists("move", $ret) === false )
	{
		$LAST_MOVE_ERROR = "didnt find [move] in return";
		$LAST_MOVE_ERROR_CODE = "fzy_missing_move";
		return false;
	}

	//devl
	//print "dumping ret=" . var_dump($ret);

	return true;

}
