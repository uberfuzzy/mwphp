<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
* action=protect *
  Change the protection level of a page.

This module only accepts POST requests.
Parameters:
  title          - Title of the page you want to (un)protect.
  token          - A protect token previously retrieved through prop=info
  protections    - Pipe-separated list of protection levels, formatted action=group (e.g. edit=sysop)
  expiry         - Expiry timestamps. If only one timestamp is set, it'll be used for all protections.
                   Use 'infinite', 'indefinite' or 'never', for a neverexpiring protection.
                   Default: infinite
  reason         - Reason for (un)protecting (optional)
                   Default: 
  cascade        - Enable cascading protection (i.e. protect pages included in this page)
                   Ignored if not all protection levels are 'sysop' or 'protect'
Examples:
  api.php?action=protect&title=Main%20Page&token=123ABC&protections=edit=sysop|move=sysop&cascade&expiry=20070901163000|never
  api.php?action=protect&title=Main%20Page&token=123ABC&protections=edit=all|move=all&reason=Lifting%20restrictions


  
  
  sample api return after posting the protect
Array
(
    [protect] => Array
        (
            [title] => CreativeWritingPositions
            [reason] => 
            [expiry] => infinity
            [protections] => Array
                (
                    [edit] => sysop
                    [move] => sysop
                )

        )

)
*/

//full protect both
function mw_api_protect_full($site, $pagename, $reason)
{
	return mw_api_set_protection($site, $pagename, $reason, 'edit=sysop|move=sysop');
}

//half protect both
function mw_api_protect_half($site, $pagename, $reason)
{
	return mw_api_set_protection($site, $pagename, $reason, 'edit=autoconfirmed|move=autoconfirmed');
}

//unprotect both
function mw_api_unprotect($site, $pagename, $reason)
{
	return mw_api_set_protection($site, $pagename, $reason, 'edit=all|move=all');
}

function mw_api_protect_create($site, $pagename, $reason, $level)
{
	return mw_api_set_protection($site, $pagename, $reason, 'create=' . $level);
}

function mw_api_set_protection($site, $pagename, $reason, $protections)
{
	global $LAST_PROTECT_ERROR;
	$LAST_PROTECT_ERROR = null;
	
	require_once('mw_api_token.php');
	$token = mw_api_get_token($site, $pagename, 'protect', true);

	$param = array();
	$param['action'] = 'protect';
	$param['title'] = $pagename;
	$param['token'] = $token;
	$param['protections'] = $protections;

	if($reason != '')
	{
		$param['reason'] = $reason;
	}
	
	require_once('mw_api_post.php');
	$fetch = mw_api_post($site, $param, true);
	
	print "fetch="; print_r($fetch);
	
	if($fetch === false)
	{
		$LAST_PROTECT_ERROR = array('FALSE returned from api_post');
		return false;
	}
	
	if( !empty($fetch['error']) )
	{
		$LAST_PROTECT_ERROR = $fetch['error'];
		return false;
	}
	
	//print "last state, return true\n";
	$LAST_PROTECT_ERROR = false;
	return true;
}

function mw_api_get_protection($site, $title)
{
	$param = array();
	$param['action'] = 'query';
	$param['prop'] = 'info';
	$param['inprop'] = 'protection';
	$param['titles'] = $title;

	$fetch = mw_api_get( $site, $param );

	if( !empty($fetch['error']) ) {
		print "error!\n";
		print_r($fetch['error']);
		return false;
	}
	
	if(  empty($fetch['query']['pages']) ) {
		print "empty pages in query\n";
		print_r($fetch);
		return false;
	}

	if( array_key_exists( '-1', $fetch['query']['pages'] ) )
	{
		print "found -1\n";
		return false;
	}

	/******************************************************************/
	foreach($fetch['query']['pages'] as $pid => $blob)
	{
		$pro = $blob['protection'];
	}

	foreach( $pro as $pid => $p )
	{
		$pro[ $p['type'] ] = $p;
		unset( $pro[$pid] );
	}

	return $pro;
}

/*
include "ez_login.php";
ez_login('ubrfzy.wikia.com', 'Uberfuzzy');
include "mw_whoami.php";
print_r(mw_who_am_i('ubrfzy.wikia.com',true));
$ret = mw_api_set_protection('ubrfzy.wikia.com', 'User:Uberfuzzy', 'testing code', 'delete=fuzzy');
var_dump($ret);
global $LAST_PROTECT_ERROR;
var_dump($LAST_PROTECT_ERROR);
*/