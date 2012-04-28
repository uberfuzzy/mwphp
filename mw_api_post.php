<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

if( defined('API_LOGIN_COOKIE_FILE') == false )
{
	//no filename defined, so set our own
	define('API_LOGIN_COOKIE_FILE', 'login.cookie');
}

/** func:
		mw_api_post

	desc:
		takes a assoc array of params, and posts to the SITE's api.php

	param:
		site [string]
			used to build url to api.php
		param_array [array]
			assoc array of parameters to send
			note: value of will be changed to 1
		login [bool][optional=true]
			flag to send login credentials

	results:
		[false]
			got non php data structure back
		[array]
			what ye asked for

	require:
		nothing? does own curl code
**/

function mw_api_post($site, $param_array, $login=true)
{
	//ALWAYS make sure its in php format, even if they send it in another way
	$param_array['format'] = 'php';

	//loop over passed params, fix nulls
	foreach($param_array as $var=>$val)
	{
		if( $val === null)
		{
			$param_array[$var] = '1';
		}
	}

	//hook for debugging
	global $LAST_POST_PARAM;
	$LAST_POST_PARAM = $param_array;

	/***************************************************************************/
	//create cURL object
	
	$ch = curl_init();

	/***************************************************************************/
	// URL stuff

	//build
	$url = "http://{$site}/api.php";

	//hook
	global $LAST_POST_URL;
	$LAST_POST_URL = $url;

	//use
	curl_setopt($ch, CURLOPT_URL, $url);
	
	/***************************************************************************/
	// set other appropriate options

	//check to to see if we need to send creds with message (default is ON)
	if( $login )
	{
		if( file_exists(API_LOGIN_COOKIE_FILE) )
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
		}
	}

	curl_setopt($ch, CURLOPT_HEADER, false);			//no headers, just body
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 	//no print, just return

	curl_setopt($ch, CURLOPT_POST, true);				//send as post, not get
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param_array);	//what to send
	#swap comment to use this if you need to post a param with a string starting with a @, and its NOT an upload
	#curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param_array));

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	//follow if redirected

	/***************************************************************************/
	// set user agent 
	$useragent="FzyBot/0.0"; 
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
	/***************************************************************************/
	// have payload, have options, so... send!
	
	//setup hooks and clear them
	global $LAST_POST_RAW, $LAST_POST_CGI;
	$LAST_POST_RAW = null;
	$LAST_POST_CGI = null;
	
	//DOEET
	$raw = curl_exec($ch);
	$cgi = curl_getinfo($ch); //quick, capture transaction data
	
	//fill debug data
	$LAST_POST_RAW = $raw;
	$LAST_POST_CGI = $cgi;

	/***************************************************************************/
	// close cURL resource
	curl_close($ch);

	/***************************************************************************/
	// use uglee methods to check what we got back
	
	//do we have a content_type to check?
	if( empty($cgi['content_type']) )
	{
		// no c_type to check
		return false;
	}
	
	//ok, there is a content type, is it the PHP encoded sig?
	if( strpos($cgi['content_type'], 'application/vnd.php.serialized') === false )
	{
		// wrong c_type
		return false;
	}

	//yup, return unwrapped data array
	return unserialize($raw);
}
