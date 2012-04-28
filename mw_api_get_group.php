<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/**	func:
		mw_api_get_group()

	params:
		site [string]
			a SITE string, passed to api_get
		group [string]
			a mediawiki permission group to be gotten

	returns:
		[false]
			*false return from api_get
			*problem in the expected structure from api_get for this call
		[array]
			single dimension array of plain user names (no User:)
			of users in the requested group

	depends:
		mw_api_get()
**/

require_once "mw_api_get.php";
 
function mw_api_get_group($site, $group)
{
	global $LAST_API_GROUP_ERROR;
	$LAST_API_GROUP_ERROR = null;

	$param = array(
	 'action' => 'query',
	   'list' => 'allusers',
	'aulimit' => '50',		//initial per fetch
	'augroup' => $group,
	);

	//pre init the outgoing storage array
	$out = array();

	//assume to keep going until told to stop;
	$continue = true;
	
	$last = '';

	while ( $continue )
	{
		//do a fetch
		$fetch = mw_api_get($site, $param);

		//do initial validity checks
		//NOTE: if found bad, DONT return, just set bad flag, and cycle
		if( is_array($fetch) == false )
		{
			//bad fetch
			$continue = false;
			continue;
		}

		if( array_key_exists('error', $fetch) )
		{
			$LAST_API_GROUP_ERROR = $fetch['error'];
			return false;
		}

		if( array_key_exists('query', $fetch) === false )
		{
			//no q in f
			$continue = false;
			continue;
		}

		if( array_key_exists('allusers', $fetch['query']) === false )
		{
			//no au in f[q]
			$continue = false;
			continue;
		}

		/********************************************/
		//if here, we know the structure is good

		//loop over data we got last, store only the usable part
		foreach($fetch['query']['allusers'] as $obj)
		{
			if( $obj['name'] == $last ) continue;
			$out[] = $obj['name'];
			$last = $obj['name'];
		}

		/********************************************/
		//loop continue checking
		if( count($fetch['query']['allusers']) == $param['aulimit'] )
		{
			// found it! extract it (doubles use as a not false)
			$continue = true;

			//change param of next loop to start here
			$param['aufrom'] = $last;
		}
		else
		{
			//no continue data
			$continue = false;
		}

	} // end of while loop

	//try to catch really bad case (WIKIA specific bug)
	if( count($out) == 1 ) {
		if( $out[0] == 'An unknown anonymous user' ) {
			$out = array();
		}
	}

	//return it
	return $out;
}

/**	func:
		mw_api_get_groups()

	desc:
		gets array of groups (that have rights) on this wiki

	params:
		site [string]
			a SITE string, passed to api_get

	returns:
		[false]
			*false return from api_get
			*problem in the expected structure from api_get for this call
		[array]
			single dimension array of group names

	depends:
		mw_api_get()

**/

function mw_api_get_groups($site)
{
	$param = array(
	 'action' => 'query',
	   'meta' => 'siteinfo',
	'siprop' => 'usergroups',
	);

	//pre init the outgoing storage array
	$out = array();

	//do fetch
	$fetch = mw_api_get($site, $param);

	//do initial validity checks
	//NOTE: if found bad, DONT return, just set bad flag, and cycle
	if( is_array($fetch) == false )
	{
		//bad fetch
		return false;
	}

	if( array_key_exists('error', $fetch) )
	{
		print_r($fetch['error']);
		return false;
	}

	if( array_key_exists('query', $fetch) === false )
	{
		//no q in f
		return false;
	}

	if( array_key_exists('usergroups', $fetch['query']) === false )
	{
		//no ug in f[q]
		return false;
	}

	/********************************************/
	//if here, we know the structure is good

	//loop over data we got last, store only the usable part
	foreach($fetch['query']['usergroups'] as $obj)
	{
		$out[] = $obj['name'];
	}

	//return it
	return $out;
}
