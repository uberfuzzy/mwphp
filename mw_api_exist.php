<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

require_once "mw_api_get.php";

function mw_api_page_exists($site, $pagename)
{
	$param = array();
	$param['action'] = "query";
	$param['prop'] = "info";
	$param['titles'] = $pagename;

	$fetch = mw_api_get($site, $param);

	if( $fetch === false )
	{
		//bigger problem here
		return false;
	}

	if( !empty($fetch['error']) )
	{
		#print "ERROR="; print_r($fetch['error']);
		return false;
	}

	//print_r($fetch);
	if( empty($fetch['query']['pages']) )
	{
		//no pages array?
		return false;
	}

	if( array_key_exists( '-1', $fetch['query']['pages'] ) )
	{
		//found a -1 key in the pages array, meaning a 'missing' title
		return false;
	}

	return true;
}

/*
checks if an IMAGE exists at a pagename.

different then above, since an IMAGE can exist without a page,
 and thus, detecting a "page" is false detection.

similar to how an NS_IMAGE page can exist without an IMAGE.
*/
function mw_api_image_exists($site, $pagename)
{
	$param = array();
	$param['action'] = "query";
	$param['prop'] = "imageinfo";
	$param['titles'] = $pagename;

	$fetch = mw_api_get($site, $param);

	//check for hard false
	if( $fetch === false )
	{
		//bigger problem here
		return false;
	}

	//check for API error messages
	if( !empty($fetch['error']) )
	{
		print "ERROR="; print_r($fetch['error']);
		return false;
	}

	//print_r($fetch);
	if( empty($fetch['query']['pages']) )
	{
		//no pages array?
		return false;
	}

	if( array_key_exists( '-1', $fetch['query']['pages'] ) )
	{
		//found a -1 key in the pages array, meaning a 'missing' title
		return false;
	}

	$p = array_shift( $fetch['query']['pages'] );

	if( array_key_exists( 'imageinfo', $p ) === false )
	{
		//no imageinfo? then theres no file here
		return false;
	}

	return true;
}
