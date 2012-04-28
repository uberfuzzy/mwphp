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

/*
 * pass in a url, get it via cURL, and return it.
 */
 
function get_raw_url($url, $login=false)
{
	// create a new cURL resource
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	
	if( $login != false && file_exists(API_LOGIN_COOKIE_FILE) )
	{
		curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
	}

	// grab URL's contents store it
	$ret = curl_exec($ch);
	$cgi = curl_getinfo($ch);
	
	// close cURL resource, and free up system resources
	curl_close($ch);

	// build results
	$out = array();
	$out['*'] = $ret;
	$out['cgi'] = $cgi;
	
	return $out;
}
