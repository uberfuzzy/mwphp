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

// give user chance to use a different filename
// define this before include file to use a non-standard name
// need to go through all things that use login data make sure they can detect this
if( defined('API_LOGIN_COOKIE_FILE') == false )
{
	//no filename defined, so set our own
	define('API_LOGIN_COOKIE_FILE', 'login.cookie');
}

/**
	func:
		mw_api_login

	desc:
		gets login creds via the API
		if stored creds are found, will see if they lead to user trying to login first.

	param:
		site [string]
			builds path to api.php
		user [string]
			duh?
		password [string]
			duh x 2

	results:
		[true]
			existing stored creds can be used to login as requested user
		[true]
			fresh login lead to login of tried user
		[false]
			(not sure if possible) login was success, but became user not who we passed
		[false]
			any one of a handful of login issues caused a non-success
			check global(LAST_LOGIN_ERROR) for reason
			check global(LAST_LOGIN_ERROR_CODE) for API error
		[false]
			got non-php data back
			NOTE: this section has raw prints, needs clean

	require:
		nothing, does own cURL calls
**/

function mw_api_login($site, $usr, $pwd, $token=null)
{
	global $LAST_LOGIN_ERROR;
	$LAST_LOGIN_ERROR = null;


	//if an existing cookie, try to use it to see if it logs the usr in
	if( file_exists(API_LOGIN_COOKIE_FILE) || !empty($token) )
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://{$site}/api.php?format=php&action=query&meta=userinfo");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	//follow if redirected
		/***************************************************************************/
		// set user agent 
		$useragent="FzyBot/0.0"; 
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
		/***************************************************************************/

		$raw = curl_exec($ch);
		$cgi = curl_getinfo($ch);

		curl_close($ch);
		unset($ch);

		if( substr($cgi['content_type'],0,30) == 'application/vnd.php.serialized')
		{
			$unser = unserialize($raw);
			//print_r($unser);
			if( strtolower($unser["query"]["userinfo"]["name"]) == strtolower($usr) )
			{
				//print "existing login was good enough\n";
				return true;
			}
		}

	}

	//fresh login
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_URL, "http://{$site}/api.php");
	$post_param = array("format"=>"php", "action"=>"login", "lgname"=>$usr, "lgpassword"=>$pwd);
	if( !empty($token) )
	{
		$post_param['lgtoken'] = $token;
	}
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param );
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE); //input
	curl_setopt($ch, CURLOPT_COOKIEJAR, API_LOGIN_COOKIE_FILE);  //output
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	//follow if redirected
	/***************************************************************************/
	// set user agent 
	$useragent="FzyBot/0.0"; 
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
	/***************************************************************************/

	$raw = curl_exec($ch);
	$cgi = curl_getinfo($ch);
	curl_close($ch);
	unset($ch);
	//file_put_contents('login.raw.txt', print_r( $raw, 1) );
	//file_put_contents('login.cgi.txt', print_r( $cgi, 1) );
	
	if( empty($raw) )
	{
		$LAST_LOGIN_ERROR = array(
			'text'=>'false curl',
			'code'=>'false_curl');
		return false;
	}

	if( empty($cgi['content_type']) )
	{
		$LAST_LOGIN_ERROR = array(
			'text'=>'missing ctype',
			'code'=>'cTypeMissing');
		return false;
	}

	if( substr($cgi['content_type'],0,30) != 'application/vnd.php.serialized')
	{
//		print "FATAL: got back unserialized api return. dumping.\n";
//		print "file: " . basename(__FILE__) . "\n";
//		print "func: " . __FUNCTION__ . "\n";
//		print "line: " . __LINE__ . "\n";
//		print_r($cgi);
//		print "\n";
		file_put_contents('login_fail.raw.txt', $raw);
		file_put_contents('login_fail.cgi.txt', print_r($cgi,true));

		$LAST_LOGIN_ERROR = array(
			'text'=> 'invalid ctype ['. $cgi['content_type'] .']',
			'code'=> 'cTypeFail');
		return false;
	}

	//ok, its safe to use
	$unser = unserialize($raw);
	#print_r($unser);
	#what was this?
	// global $LAST_LOGIN_DATA;
	// $LAST_LOGIN_DATA = $unser['login'];

	$rc = $unser['login']['result'];
	switch( $rc )
	{
		case "Success":
			if( strtolower($unser['login']['lgusername']) == strtolower($usr) )
			{
				//print "username match\n";
				return true;
			}
				//we logged in, but the resulting user was not the one we thought we would get
				//not sure how this would happen, but we failed to do what we were supposed to
				$LAST_LOGIN_ERROR = $unser;
				return false;
		case 'Throttled';
			$LAST_LOGIN_ERROR = array(
				'text'=>'Throttled, wait ' . $unser['login']['wait'],
				'code'=>'Throttled',
				'meta'=>$unser['login']['wait']);
			return false;
			break;
		default:
			//print "wtf, default case [{$rc}]\n";
		case 'EmptyPass';
		case 'Illegal';
		case 'NoName';
		case 'NotExists':
		case 'WrongPass';
		case 'WrongPluginPass';
			//print "bad login result [{$rc}]\n";
			//print "dump unser="; print_r($unser); print "\n";
			$LAST_LOGIN_ERROR = array(
				'text'=>"various error [{$rc}]",
				'code'=>$rc);
			return false;
		case 'NeedToken':
			//case for MW 1.15.3+
			$token = $unser['login']['token'];
			return mw_api_login($site, $usr, $pwd, $token);
			break;
	}

        //end of login func, should not hit this line at all
}

/**
	func:
		api_logout

	desc:
		does a server logout via the API, wipes session data

	param:
		site [string]
			builds path to api.php
		clean [bool][optional]
			default: false
			flag to make the disk file to be deleted also

	results:
		[null]
			existing stored creds can be used to login as requested user

	require:
		nothing, does own cURL calls
**/

function mw_api_logout($site, $clean=false)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_URL, "http://{$site}/api.php");
	curl_setopt($ch, CURLOPT_POSTFIELDS, "format=php&action=logout");
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, API_LOGIN_COOKIE_FILE);
	curl_setopt($ch, CURLOPT_COOKIEJAR, API_LOGIN_COOKIE_FILE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	/***************************************************************************/
	// set user agent 
	$useragent="FzyBot/0.0"; 
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
	/***************************************************************************/

	$raw = curl_exec($ch);

	curl_close($ch);

	if( $clean != false )
	{
		//try to delete file too

		//return bool on attempt of delete
		return unlink(API_LOGIN_COOKIE_FILE);
	}

	//the normal return
	return null;
}
