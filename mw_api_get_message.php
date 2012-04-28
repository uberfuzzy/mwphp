<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/** func:
		api_get_message

	desc:
		gets a mediawiki message text from the wiki,
		better then getting the MW: page text direct,
		so you can get the messaging default if non-exist

	param:
		site [string]
			an api site string
		message [*]
			[string]
				single message to get
			[string]
				pipe smushed list of messages to get
			[array]
				single dimension array of messages to get,
				will get pipe smushed anyway

	return:
		[false]
			failed one of the basic api return valid checks
		[array]
			if requested multiple, get array back, missings will be bool false
		[string]
			ask for 1, get 1

	require:
		api_get
**/

require_once "mw_api_get.php";

function mw_api_get_message($site, $message, $lang=null)
{
	global $LAST_MESSAGE_ERROR, $LAST_MESSAGE_ERROR_CODE;
	$LAST_MESSAGE_ERROR = $LAST_MESSAGE_ERROR_CODE = null;

	if( is_array($message) )
	{
		$message = array_implode('|', $message);
	}

	$param = array('action' => 'query',
					'meta' => 'allmessages',
					'ammessages' => $message);

	if($lang != null)
	{
		$param['amlang'] = $lang;
	}

	$fetch = mw_api_get($site, $param);
	#print __METHOD__ . " var_dump(fetch)="; var_dump($fetch);

	if( $fetch === false )
	{
		$LAST_MESSAGE_ERROR = "hard api_get false";
		$LAST_MESSAGE_ERROR_CODE = "fzy_hardfalse";
		return false;
	}
	if( array_key_exists('error', $fetch) )
	{
		$LAST_MESSAGE_ERROR = "found error from get [{$fetch['error']['code']}]->[{$fetch['error']['info']}]";
		$LAST_MESSAGE_ERROR_CODE = $fetch['error']['code'];
		return false;
	}
	if( array_key_exists('query', $fetch) === false )
	{
		$LAST_MESSAGE_ERROR = "no query in fetch";
		$LAST_MESSAGE_ERROR_CODE = "fzy_apifail";
		return false;
	}
	if( array_key_exists('allmessages', $fetch['query']) === false )
	{
		$LAST_MESSAGE_ERROR = "no allmessages in fetch(query)";
		$LAST_MESSAGE_ERROR_CODE = "fzy_apifail";
		return false;
	}

	//take out what we need
	$am = $fetch['query']['allmessages'];
	//throw away the rest
	unset($fetch);

	if( count($am) == 1 )
	{
		//just one (most likely)
		$pop = array_pop($am);

		//test for missing
		if( array_key_exists('missing', $pop) )
		{
			//sorry, please try again
			$LAST_MESSAGE_ERROR = "missing? @".__LINE__;
			$LAST_MESSAGE_ERROR_CODE = "fzy_apifail";
			return false;
		}

		//ask and ye shall recieve
		return $pop['*'];
	}
	elseif( count($am) > 1 )
	{
		//multiple
		$out = array();
		foreach($am as $foo)
		{
			//print_r($foo);
			if( array_key_exists('missing', $foo) )
			{
				$out[ $foo['name'] ] = false;
				continue;
			}
			$out[ $foo['name'] ] = $foo['*'];
		}
		return $out;
	}
	else
	{
		//none?
		die( __METHOD__ . "\n" . print_r($am, true) );
	}

}
