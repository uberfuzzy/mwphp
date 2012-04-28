<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/** func:
		mw_api_delete
		
	desc:
		deletes a page using apiwrite call
		
	params:
		site [string]
			root path, used in api calls
		target [string]
			-the pagename to delete
			-the page id to delete (set by_id to true)
		summary [string][optional]
			default: ''
			the summary used for log
		by_id [bool][optional]
			default: false
			flag to make target be used as page ID, not pagename

	return:
		[true]
			delete was ok!
		[false]
			error happened
			-error cause will be stored in global LAST_DELETE_ERROR
			
	
	depend on:
		mw_api_get_token
			for getting the delete token for the pagename
		mw_api_post
			for sending the post to api.php
			
	notes:
		*both token and post call will attempt to use login cookie if found.
		please login before trying to delete (duh?)
*/

require_once "mw_api_post.php";
require_once "mw_api_token.php";

function mw_api_delete($site, $target, $summary='', $by_id=false)
{
	//hook and nulify errors (for optional external debugging).
	global $LAST_DELETE_ERROR;
	global $LAST_DELETE_ERROR_CODE;
	$LAST_DELETE_ERROR = null;
	$LAST_DELETE_ERROR_CODE = null;

	//build param array
	$param = array();
	$param['action'] = 'delete';

	if( empty($by_id) )
	{
		$param['title']  = $target;
	}
	else
	{
		$param['pageid']  = (int)$target;
	}
	$param['reason'] = $summary;

	//attempt to get a token.
	// note: we dont try to detect bad tokens anymore, let API complain.
	$param['token']  = mw_api_get_token($site, $target, 'delete');

	//post!
	$ret = mw_api_post($site, $param, true);
	
	//check for false return
	if($ret === false)
	{
		$LAST_DELETE_ERROR = 'api post was FALSE';
		$LAST_DELETE_ERROR_CODE = 'false_post';
		return false;
	}
	
	//check for error code in api result
	if( !empty($ret['error']) )
	{
		$LAST_DELETE_ERROR = 'found api error [' . $ret['error']['code'] . ']->['. $ret['error']['info'] .']';
		$LAST_DELETE_ERROR_CODE = $ret['error']['code'];
		return false;
	}

	//not the best, but i dont know what else todo
	return true;
}


/** func:
		mw_api_undelete
		
	desc:
		undeletes a page using api call
		
	params:
		site [string]
			root path, used in api calls
		pagename [string]
			-the pagename to undelete
		reason [string][optional]
			default: ''
			the summary used for log
		timestamps [array?][optional]
			default: null
			leave null to undelete all revisions

	return:
		[true]
			undelete was ok!
		[false]
			error happened
			-error cause will be stored in global LAST_UNDELETE_ERROR
			
	
	depend on:
		mw_api_get_token
			for getting the undelete token for the pagename
		mw_api_post
			for sending the post to api.php
			
	notes:
		*both token and post call will attempt to use login cookie if found.
		please login before trying to undelete (duh?)
*/


function mw_api_undelete($site, $pagename, $reason='', $timestamps=null)
{
	$param = array('action' => 'undelete');
	$param['title'] = $pagename;
	$param['reason'] = $reason;
	$param['token'] = mw_api_get_undeletetoken($site, $pagename);
	
	if($timestamps != null)
	{
		$param['timestamps'] = $timestamps;
	}
	
	require_once('mw_api_post.php');
	$fetch = mw_api_post($site, $param, true);

	if($fetch == false)
	{
		return false;
	}
	
	if( !empty($fetch['error']) )
	{
		return $fetch['error'];
	}
	
	return true;
}
