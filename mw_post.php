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

if( defined('API_LOGIN_COOKIE_FILE') == false )
{
	//no filename defined, so set our own
	define('API_LOGIN_COOKIE_FILE', 'login.cookie');
}

/*
$payload = array();
$payload['wpSection'] = 'new';
$payload['wpTextbox1'] = $content;
$payload['wpSummary']  = $sec_title;
$payload['wpEditToken'] = $token;
$payload['wpMinoredit'] = 'on';

$wpTime = gmdate('YmdHis');
$payload['wpStarttime'] = $wpTime;
$payload['wpEdittime'] = $wpTime;

$lastedit = api_get_timestamp($site, $page);
$payload['wpEdittime'] = gmdate('YmdHis', strtotime($lastedit));


$url = "{$site}/index.php?action=edit&title=" . urlencode($page);

print "-----------------------------------\n";
$raw = mw_post($url, $payload, true);

*/

function mw_post($url, $payload_array, $login=false)
{
	$pairs = array();
	foreach($payload_array as $var=>$val)
	{
		if( $val !== null)
		{
			$pairs[] = urlencode($var) . '=' . urlencode($val); 
		}
		else
		{
			$pairs[] = urlencode($var); 
			//die("single param in " . __METHOD__ . "\n" . print_r($payload_array, true));
		}
	}
	$payload_string = implode('&', $pairs);
/***************************************************************************/
	$ch = curl_init();
	// set URL and other appropriate options

	global $LAST_API_URL;
	$LAST_API_URL = $url;

	curl_setopt($ch, CURLOPT_URL, $url);

/***************************************************************************/
	if($login != false)
	{
		if( file_exists(API_LOGIN_COOKIE_FILE) )
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
		}
	}

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);

	// grab URL and pass it to the browser
	$raw = curl_exec($ch);

	global $LAST_CURL_CGI;
	$LAST_CURL_CGI = curl_getinfo($ch);
/***************************************************************************/
	// close cURL resource, and free up system resources
	curl_close($ch);

	//print "normal, returning raw... ";
	return $raw;
}