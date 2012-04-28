<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/******************************************************************************
	func:
		mw_api_is_redirect

	desc:
		pass a pagetitle, get a bool back of is redirect page

	param:
		site [string]
			api site path
		pagename [string]
			full pagename of page to check

	return:
		[null]
			api low level fetch fail?
		[true]
			found redirect flag, is a redirect page
		[false]
			got data, but no redir flag
			
	require:
		mw_api_get
			included on-demand
**/

function mw_api_is_redirect($site, $pagename)
{
	$param = array();
	$param['action'] = 'query';
	$param['prop'] = 'info';
	$param['titles'] = $pagename;
	
	require_once "mw_api_get.php";
	$fetch = mw_api_get($site, $param);
	
	//NOTE: these return NULL, not FALSE
	
	//bad fetch?
	if($fetch === false) { return null; }
	
	//detect pages before using it
	if( array_key_exists('query', $fetch) === false ) { return null; }
	if( array_key_exists('pages', $fetch['query']) === false ) { return null; }
	
	//pagename not exist?
	if( array_key_exists('-1', $fetch['query']['pages']) === true ) { return null; }
	
	//pull it out
	$page = array_shift($fetch['query']['pages']);
	
	//look at it
	if( array_key_exists('redirect', $page) !== false )
	{
		//found it, means its a redirect
		return true;
	}
	else
	{
		//not found it, its not a redirect
		return false;
	}
}


/******************************************************************************
	func:
		mw_api_resolve_redirect

	desc:
		pass a pagename, follow all redirects (in code) and return final pagename

	param:
		site [string]
			api site path
		pagename [string]
			full pagename of page to check
		same [bool][byref]
			(optional, default: null)
			pass in a flag to catch if the input matched the output
		double [bool][byref]
			(optional, default: false)
			pass in flag to catch if a false return means found double redirect
		loop [bool][byref]
			(optional, default: false)
			pass in flag to catch if a false return means found redirect loop
			

	return:
		[null]
			api low level fetch fail?
		[string]
			redirect resolved pagename
				might be the same as input if was not a redirect

	require:
		mw_api_get
**/

function mw_api_resolve_redirect($site, $pagename, &$same = false, &$double = false, &$loop = false)
{
	$param = array();
	$param['action'] = 'query';
	$param['prop'] = 'info';
	$param['titles'] = $pagename;
	$param['redirects'] = null;
	
	require_once "mw_api_get.php";
	$fetch = mw_api_get($site, $param);
	print_r($fetch);
	
	//safety checks
	if( $fetch === false ) { return null; }
	if( array_key_exists('query', $fetch) == false ) { return null; }
	
	//no pages means redirect loop
	if( array_key_exists('pages', $fetch['query']) == false )
	{
		//print "no pages in query\n";
		$loop = true;
		return false;
	}

	//has pages, not a loop, pop it out of the array to work with it
	$page = array_shift($fetch['query']['pages']);
	
	if( array_key_exists('redirects', $fetch['query']) )
	{
		//has some redirects
		
		if( count($fetch['query']['redirects']) > 1 )
		{
			//more then one, means a double redirect, so flag it.
			$double = true;
		}
	}
	//done with this, free it
	unset($fetch);

	//mark a bit if the input matchs the output
	if( $page['title'] == $pagename )
	{
		$same = true;
	}
	else
	{
		$same = false;
	}
	
	//return what they want, the 'final' name
	return $page['title'];
}

