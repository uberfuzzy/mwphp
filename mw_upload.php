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

function mw_upload($site, $destname, $filename, $summary='')
{
	$url = "{$site}/wiki/Special:Upload";

	$payload = array(
		"wpUploadFile" => '@' . $filename,
		"wpSourceType" => "file",
		"wpDestFile" => $destname,
		"wpUploadDescription" => $summary,
		"wpLicense" => "",
		"wpIgnoreWarning" => "1",
		"wpUpload" => "Upload file",
		"wpDestFileWarningAck" => "1",
	);

	global $LAST_UPLOAD_PARAM;
	$LAST_UPLOAD_PARAM = $payload;
/***************************************************************************/
	$ch = curl_init();
	// set URL and other appropriate options

	curl_setopt($ch, CURLOPT_URL, $url);

/***************************************************************************/
	if( file_exists(API_LOGIN_COOKIE_FILE) )
	{
		curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
	}

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

	// grab URL and pass it to the browser
	$raw = curl_exec($ch);

	global $LAST_UPLOAD_CGI;
	$LAST_UPLOAD_CGI = curl_getinfo($ch);
/***************************************************************************/
	// close cURL resource, and free up system resources
	curl_close($ch);

	//print "normal, returning raw... ";
	return $raw;

}
