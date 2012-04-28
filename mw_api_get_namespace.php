<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

require_once "mw_api_get.php";

/** func:
		mw_api_get_namespace

	desc:
		pass integer of namespace to get string of namespace
		note: will actually get ALL namespaces, then only return on you want

	param:
		site [STRING]
			base of url to api
		NS [INTEGER]
			number of namespace you want

	return:
		[false]
			problem with api_get return
		[string]	
			string of namespace you asked for

	require:
		mw_api_get_namespaces
**/
function mw_api_get_namespace($site, $NS)
{
	//get all the namespaces at this site
	$fetch = mw_api_get_namespaces($site);

	//check for problem in the return
	if($fetch === false) { return false; }

	//make sure its in the returned list
	if( array_key_exists($NS, $fetch) )
	{
		//it is, so return the string
		return $fetch[$NS];
	}

	//wasnt in the list, so fail
	return false;
}

/** func:
		mw_api_get_namespaces

	desc:
		returns a list of all namespaces at a wiki in assoc array.

	param:
		site [STRING]
			base of url to api

	return:
		[false]
			problem with api_get return
		[array]	
			array of namespaces as this wiki.
			namespace integer number as key.
			namespace text as value.
			
	require:
		mw_api_get
**/
function mw_api_get_namespaces($site)
{
	$nsparams = array();
	$nsparams['action'] = 'query';
	$nsparams['meta'] = 'siteinfo';
	$nsparams['siprop'] = 'namespaces';

	$fetch = mw_api_get($site, $nsparams);

	//check for basic false return from api_get
	if( $fetch === false ) { return false; }

	//check for expected structure.
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('namespaces', $fetch['query']) === false ) { return false; }

	//by here, we know the return is a certain way,
	//so just keep what we need
	$fetch = $fetch['query']['namespaces'];

	//loop over the array of namespaces
	foreach($fetch as $id=>$arr)
	{
		if($id < 0)
		{
			//this filters out all of the 'meta' spaces like special:
			unset($fetch[$id]);
			continue;
		}

		//changes the array of data to only keep the string part of it
		$fetch[$id] = $arr['*'];
	}

	//its all scrubbed, so return it.
	return $fetch;
}

/*
function to get all the pages in a namespace
*/

function mw_api_get_namespace_contents($site, $ns=0, $max=null, $redirects=null)
{
	$param = array();
	$param['action'] = 'query';
	$param['list'] = 'allpages';
	$param['apnamespace'] = $ns;

	$param['aplimit'] = 500; //per fetch

	switch($redirects)
	{
		case 'r':
			$param['apfilterredir'] = 'redirects';
			break;
		case 'nr':
			$param['apfilterredir'] = 'nonredirects';
			break;
		default:
			//all
	}

	$out = array();
	$continue = true;
	while($continue)
	{
		$continue = false;

		$fetch = mw_api_get($site, $param);
		//print_r($fetch);

		if( empty($fetch) ){ print __LINE__; return false; }
		
		if( !empty($fetch['error']) ){ print_r($fetch['error']); print __LINE__; return false; }

		if( !empty($fetch['query-continue']) &&
			!empty($fetch['query-continue']['allpages']))
		{	//found con
			$continue = $fetch['query-continue']['allpages']['apfrom'];
			$param['apfrom'] = $continue;
		}
		else
		{	//no con
			$continue = false;
		}

		if( !empty($fetch['query']) && !empty($fetch['query']['allpages']) )
		{
			//print "got " . count($fetch['query']['allpages']) . "\n";;
			foreach($fetch['query']['allpages'] as $meh=>$wad)
			{
				$out[] = $wad['title'];
				//print "pagename = " . $wad['title'] . "\n";
			}

			if( $max != null && count($out) > $max )
			{
				//var_dump($out);
				//print "out is now over requested max\n";
				$out = array_slice($out, 0, $max);
				$continue = false;
			}
		}
		else
		{
			//no pages? stop i guess
			$continue = false;
		}

	}

	//print "returning " . count($out) . " elements\n";
	return $out;
}