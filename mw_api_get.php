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

/** func:
		mw_api_get

	purpose:
		takes a array of url params, formats into a url,
		and uses curl to get that url, returning as a php array

	param:
		site [string]
			the base part of url, leading to api.php
		param_array [array]
			assoc array of parameter and values
			note: nulls will get converted to 1
		login [bool][optional=false]
			flag to try to load existing login data.
			must exist already

	requires:
		none, does own cURL calls
**/

function mw_api_get($site, $param_array, $login=false)
{
	//ALWAYS make sure its in php format, even if they send it in another way
	$param_array['format'] = 'php';

	//check, encode and smush
	$param_pairs = array();
	foreach($param_array as $var=>$val)
	{
		if( $val === null)
		{
			//they passed a null, so change to a 1
			$val = '1';
		}
			$param_pairs[] = urlencode($var) . '=' . urlencode($val);
	}

	$param_string = implode('&', $param_pairs);
	unset($param_pairs); //cleanup

	/**************************************************************************/
	/** If they are still using the old format, and passing a full url as site,
		this is bad, so we need to trim it down.
		Use the very ugly parse_url() to get the middle part,
		sucks if your a non-standard path
	**/
	if( strpos($site, 'http://') !== false)
	{
		$site = parse_url($site,PHP_URL_HOST);
	}

	/**************************************************************************/
	/** create our curl object
	**/
	$ch = curl_init();

	/**************************************************************************/
	/** build up the actuall url we're going to curl
	**/

	$url = "http://{$site}/api.php?" . $param_string;
	unset($tmp);

	// save this url into a global, for external debugging/logging
	global $LAST_GET_URL;
	$LAST_GET_URL = $url;

	//set the curl object to use this url
	curl_setopt($ch, CURLOPT_URL, $url);

	/**************************************************************************/
	/** set URL and other appropriate options
	**/
	// if login bit is on, try to use stored login data
	if($login != false)
	{
		if( file_exists(API_LOGIN_COOKIE_FILE) )
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
		}
	}

	//we dont want any of the headers when doing these fetchs, just the data
	curl_setopt($ch, CURLOPT_HEADER, false);

	//we want to capture the data returned, not screen dumped
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	/***************************************************************************/
	// set user agent (some external wikis refuse if no agent)
	$useragent="FzyBot/0.0"; 
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
	/***************************************************************************/
	//ready to run!

	//setup debug hooks
	global $LAST_GET_RAW;
	global $LAST_GET_CGI;
	$LAST_GET_RAW = $LAST_GET_CGI = null;

	// get
	$raw = curl_exec($ch);
	$cgi = curl_getinfo($ch); //capture transaction data;
	
	// stash
	$LAST_GET_RAW = $raw;
	$LAST_GET_CGI = curl_getinfo($ch);

	/***************************************************************************/
	// close cURL resource, and free up system resources
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
