<?php
/******************************************************************************
    Copyright 2008-2010 Christopher L. Stafford

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
