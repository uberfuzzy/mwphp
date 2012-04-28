<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/***************************************************
 * DEFUNCT CODE!!! DO NOT USE. CONVERT TO API ASAP *
 ***************************************************/

require_once "mw_post.php";
require_once "mw_api_token.php";

function mw_page_delete($host, $page, $summary)
{
	/** get a delete token **/
	$token = mw_api_get_token($host, $page, 'delete');
	
	if( $token == false )
	{
		print "error getting delete token\n";
		return false;
	}
	
	/** ok, we have a delete token **/
	
	/*
	action=delete
	wpEditToken=$token
	wpReason="clearing old default page to allow new format"
	*/

/**********************************************************/
	$payload = array();
	$payload['wpEditToken'] = $token;
	$payload['wpReason'] = $summary;

	$url = "http://" . $host . "/index.php?action=delete&title=" . urlencode($page);
	//print_r($url); print "\n";
	$raw = mw_post($url, $payload, true);
	
	//file_put_contents("cache/{$host}.html", $raw);
	if( strpos( $raw, "Internal error" ) !== false)
	{
		return false;
	}
	
	return true;
/**********************************************************/	
}

?>