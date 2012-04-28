<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/** func:
		resolve_url

	desc:
		uses php function to peek at headers of url,
		following location redirects, until finding 'last' url

	param:
		url [string]
			URL of where to start from

	return:
		[false]
			internal function returned false, nothing to recover
		[string]
			final destination

	require:

	to do/notes:
		add ghetto cache like parent version?
		maybe add param to return whole location array, and not pop?
**/

function resolve_url($in_url)
{
	//get some http data
	$headers = @get_headers($in_url, true);
	//print_r($headers);
	if($headers === false){ return false; }

	if( array_key_exists('Location', $headers) === false )
	{
		//no redirects happened, this IS the final
		return $in_url;
	}

	// redirects happened
	if( is_array($headers['Location']) )
	{
		//more then one, pop last.
		$out_url = array_pop($headers['Location']);
	}
	else
	{
		//just one, copy it.
		$out_url = $headers['Location'];
	}

	//throw away the rest
	unset($headers);

	//give back that last one
	return $out_url;
}
