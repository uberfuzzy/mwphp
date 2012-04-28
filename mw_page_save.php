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

require_once "mw_post.php"; // for the post
require_once "mw_api_token.php"; // to get the edit token
require_once "mw_api_getpage.php"; // to get the last edit timestamp of the page (edit conflict crap)

/*
note:
does not have section edit support(yet)
does not have new section support (yet)
all edits marked as minor
*/

function mw_page_save($site, $pagename, $text, $summary)
{
	//print "in " . __METHOD__ . "\n";
	
	$token = mw_api_get_token($site, $pagename, 'edit');
	if($token == false)
	{
		//print "token fail\n";
		return false;
	}
	
	$payload = array();
	$payload['action'] = 'edit';
	$payload['title'] = $pagename;
		
	$payload['wpTextbox1'] = $text;
	$payload['wpSummary']  = $summary;
	$payload['wpEditToken'] = $token;
	$payload['wpMinoredit'] = 'on';

	$wpTime = gmdate('YmdHis');
	$payload['wpStarttime'] = $wpTime;
	$payload['wpEdittime'] = $wpTime;

	$lastedit = mw_api_get_page_timestamp($site, $pagename);
	//print "le [$lastedit]\n";
	$payload['wpEdittime'] = gmdate('YmdHis', strtotime($lastedit));

	// print "-----------------------------------\n";
	// print "payload ready\n";
	// print_r($payload);
	// print "-----------------------------------\n";
	
	$url = "http://{$site}/index.php";
	// print "url [$url]\n";
	
	// print "-----------writing-----------------\n";
	$raw = mw_post($url, $payload, true);
	// print "-----------------------------------\n";
	
	// print "ret = " . ( ($raw)?('true'):('false') ) . "\n";
	// print "\n\n";
	
	// file_put_contents('raw.html', $raw);
	// global $LAST_CURL_CGI;
	// print_r($LAST_CURL_CGI);
	
	sleep(1);
	return true;
}
