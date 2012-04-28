<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/** func:
		mw_api_save

	desc:
		uses apiwrite function to saves a text to a page

	param:
		site [string]
			site string, path to api
		pagename [string]
			page to save the text too
		pagetext [string]
			text to save
		summary [string][optional]
			default: ''
			optional edit summary
		options [array][optional]
			default: null
			optional array of optional bits
				'b','bot' == mark as bot
				'm','minor == mark as minor
				'section'=>'new' == post to a new section
				'section'=> # == post to a numbered section (integer only please)
				'w','watch' == watch this page
				'uw','unwatch' == unwatch (or force to not watch)
				'prepend' = make the passed text be added to top of the page
					note: disables section flags
				'append' = make the passed text be added to bottom of the page
					note: disables section flags
				'co','create','createonly' = create only
				'nc','nocreate','dontcreate' = only edit if exist
				'undo' = <i forget, but i know it disables the text input>

	return:
		[false]
			failed to save
			note: helpful error message stored in global LAST_SAVE_ERROR
			note: actual api error code stored in global LAST_SAVE_ERROR_CODE
		[true]
			saved correctly

	require:
		mw_api_token
			to get the edit token for the page
		mw_api_post
			to send the stuff to the place

	to do/notes:
**/
require_once "mw_api_token.php";
require_once "mw_api_post.php";

function mw_api_save($site, $pagename, $pagetext, $summary='', $options = array())
{
	//setup and reset the hooks
	global $LAST_SAVE_ERROR;      $LAST_SAVE_ERROR = null; 
	global $LAST_SAVE_ERROR_CODE; $LAST_SAVE_ERROR_CODE = null; 
	//TODO: other then E and E_C, do i need all of these?
	global $LAST_SAVE_TOKEN;      $LAST_SAVE_TOKEN = null;
	global $LAST_SAVE_PARAM;      $LAST_SAVE_PARAM = null;
	global $LAST_SAVE_RAW;        $LAST_SAVE_RAW = null;

	$token = mw_api_get_token($site, $pagename, 'edit');
	$LAST_SAVE_TOKEN = $token;
	//TODO: should I detect bad token? or let API?
	if($token === false) { $LAST_SAVE_ERROR = "bad token; =false"; return false; }

	$param = array();
	$param['action']  = 'edit';
	$param['title']   = $pagename;
	$param['text']    = $pagetext;
	$param['token']   = $token;
	$param['summary'] = $summary;

	if( !empty($options) && is_array($options) )
	{
		//TODO: array_seach or in_array ?
		//TODO: maybe array_intersect?
		//TODO: use array_key_exists?
		if( array_search('minor', $options) !== false
		||  array_search('m',     $options) !== false )
		{
			$param['minor'] = 1;
		}

		if( array_search('bot', $options) !== false
		||  array_search('b',   $options) !== false )
		{
			$param['bot'] = 1;
		}

		if( array_search('watch', $options) !== false
		||  array_search('w',     $options) !== false )
		{
			$param['watch'] = 1;
		}

		if( array_search('unwatch', $options) !== false
		||  array_search('uw',      $options) !== false )
		{
			$param['unwatch'] = 1;
			if( !empty($param['watch']) )
			{
				unset($param['watch']);
			}
		}

		if( !empty( $options['section'] ) )
		{
			$param['section'] = $options['section'];
		}

		if( array_search('prepend', $options) !== false )
		{
			//since doing prepends, force sections off
			if( !empty($options['section']) )
			{
				unset($param['section']);
			}

			//since doing prepend, force use different text field
			$param['prependtext'] = $pagetext;
			unset($param['text']);
		}

		if( array_search('append', $options) !== false )
		{
			//since doing prepends, force sections off
			if( !empty($options['section']) )
			{
				unset($param['section']);
			}

			//since doing prepend, force use different text field
			$param['appendtext'] = $pagetext;
			unset($param['prependtext']);
			unset($param['text']);
		}

		if( array_search('create',     $options) !== false
		||  array_search('createonly', $options) !== false
		||  array_search('co',         $options) !== false )
		{
			$param['createonly'] = 1;
		}

		if( array_search('nocreate',   $options) !== false
		||  array_search('dontcreate', $options) !== false 
		||  array_search('nc',         $options) !== false )
		{
			$param['nocreate'] = 1;
		}

		if( !empty($options['undo']) )
		{
			$param['undo'] = $options['undo'];
			unset($param['text']);
			unset($param['appendtext']);
			unset($param['prependtext']);
		}
	}
	// end option checking

	// back this up to external hooked var for debugging
	$LAST_SAVE_PARAM = $param;

	// lets do it
	$ret = mw_api_post($site, $param, true);
	$LAST_SAVE_RAW = $ret;

	if($ret === false)
	{
		//oh noes, a hard false? thats bad

		//simple error message
		$LAST_SAVE_ERROR = "false from post";
		$LAST_SAVE_ERROR_CODE = "fzy_false_post";

		global $LAST_POST_RAW, $LAST_POST_CGI;
		//is there a http error code?
		if( !empty($LAST_POST_CGI['http_code']) )
		{
			//yes, lets use it to build a better fail message
			$LAST_SAVE_ERROR = "false from post [http={$LAST_POST_CGI['http_code']}".
								", bytes=". strlen($LAST_POST_RAW) ."]";
		}

		return false;
	}

	//is there an error section in the package?
	if( !empty($ret['error']) )
	{
		$LAST_SAVE_ERROR = "found error from post [{$ret['error']['code']}]->[{$ret['error']['info']}]";
		$LAST_SAVE_ERROR_CODE = $ret['error']['code'];
		return false;
	}

	if( array_key_exists("edit", $ret) === false )
	{
		$LAST_SAVE_ERROR = "didnt find edit in return (".__LINE__.")";
		$LAST_SAVE_ERROR_CODE = "fzy_structure_fail_".__LINE__;
		return false;
	}

	if( array_key_exists("result", $ret['edit']) === false )
	{
		$LAST_SAVE_ERROR = "didnt find result in edit (".__LINE__.")";
		$LAST_SAVE_ERROR_CODE = "fzy_structure_fail_".__LINE__;
		return false;
	}

	if( $ret['edit']['result'] !== "Success" )
	{
		//DEV: really could use better checking here, but needs more testing
		$LAST_SAVE_ERROR = "result != success [{$ret['edit']['result']}]";
		$LAST_SAVE_ERROR_CODE = "fzy_result_not_success";
		return false;
	}

	// nothing else failed, so... ok?
	return true;
}

/*
EXAMPLES:

success return
array(1) {
  ["edit"]=>  array(4) {
    ["result"]=>    string(7) "Success"
    ["pageid"]=>    string(4) "5591"
    ["title"]=>    string(13) "UberfuzzyTest"
    ["nochange"]=>    string(0) ""
  }
}

fail return
array(1) {
  ["error"]=>  array(2) {
    ["code"]=>    string(8) "badtoken"
    ["info"]=>    string(13) "Invalid token"
  }
}
*/
