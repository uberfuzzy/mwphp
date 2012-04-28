<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

function TwitterPost($user, $pass, $message)
{
	// create a new cURL resource
	$ch = curl_init();
	
/***************************************************************************/
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, 'http://twitter.com:80/statuses/update.xml');

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$pass}");
	curl_setopt($ch, CURLOPT_POSTFIELDS, "status=". urlencode($message) );

	// grab URL and pass it to the browser
	$raw = curl_exec($ch);

	global $last_getinfo;
	$last_getinfo = curl_getinfo($ch);

	global $last_tweet_raw;
	$last_tweet_raw = $raw;
	
/***************************************************************************/
	// close cURL resource, and free up system resources
	curl_close($ch);

	//print "normal, returning raw... ";
	return $raw;
}

?>