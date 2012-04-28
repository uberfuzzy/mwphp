<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

require_once "mw_api_get.php";

/**** examples
$site = 'www.wikia.com';

$ret = mw_api_get_logs($site, 'pr_rep_log', 10);
print_r($ret);

#gets ONLY undelete events, but not delete (both are in the delete logs)
$ret = mw_api_get_logs($site, array('delete','undelete'), 10);
print_r($ret);

*/
//$ret = mw_api_get_logs_for_person($site, 'Uberfuzzy', null/*type*/, 0 /*lmit*/);
//$ret = mw_api_get_logs_for_title($site, 'Contact us', 'pr_rep_log'/*type*/ );
//print_r($ret);

function mw_api_get_logs($site, $type=null, $limit=null, $bywho=null, $forpage=null)
{
	$all = false;
	$param = array();
	$param['action'] = 'query';
	$param['list'] = 'logevents';
	$param['leprop'] = 'ids|title|type|user|timestamp|comment|details';

	if($type != null)
	{
		if( is_array($type) )
		{
			$param['letype'] = $type[0];
			$le_action = $type[1];
		}
		else
		{
			$param['letype'] = $type;
		}
	}

	if($bywho != null)
	{
		$param['leuser'] = $bywho;
	}

	if($forpage != null)
	{
		$param['letitle'] = $forpage;
	}

	//print "limit="; var_dump($limit);
	if($limit !== null)
	{
		//passed a limit.
		if($limit == 0)
		{
			//caller wants 'all'

			//set amount per cycle
			$param['lelimit'] = 50;

			//set flag to loop;
			$all = true;
		}
		else
		{
			//passed a limit, so give only that many
			$param['lelimit'] = (int)$limit;
		}
	}
	else
	{
		//no limit passed, safe to assume to give them 1 "page"
		$param['lelimit'] = 50;
	}
	//print "p(limit)="; var_dump($param['lelimit']);

	$continue = true;

	$out = array();
	while( $continue )
	{
		$fetch = mw_api_get($site, $param);
		//print_r($fetch);

		if($fetch === false)
		{
			#print "bad fetch, stopping\n";
			$continue = false;
			continue;
		}

		//HACK
		//attempt to check for the message saying this log type is no good here
		//mediawiki is a douche, it doesnt stop on bad type,
		// it just whines, and then returns ALL THE LOGS AT THE WIKI
		if( !empty($fetch['warnings']['logevents']) )
		{
			if($fetch['warnings']['logevents']['*'] == "Unrecognized value for parameter 'letype': " . $type)
			{
				return false;
			}
		}

		if( array_key_exists('query', $fetch) === false )
		{ $continue = false; continue; }

		if( array_key_exists('logevents', $fetch['query']) === false )
		{ $continue = false; continue; }

		if($all)
		{
			if( array_key_exists('query-continue', $fetch) == true )
			{
				//print "continue="; print_r($fetch['query-continue']);
				$continue = $fetch['query-continue']['logevents']['lestart'];
				$param['lestart'] = $continue;
			}
			else
			{
				$continue = false;
			}

			foreach($fetch['query']['logevents'] as $le)
			{
				$out[] = $le;
			}
		}
		else
		{
			$out = $fetch['query']['logevents'];
			$continue = false;
		}

	}

	if( !empty($le_action) )
	{
		foreach($out as $id=>$le)
		{
			if($le['action'] != $le_action)
			{
				unset($out[$id]);
			}
		}
	}

	//print count($out);
	return $out;
}

function mw_api_get_logs_for_title($site, $pagename, $type=null, $limit=null)
{
	return mw_api_get_logs($site, $type, $limit, null /*who*/, $pagename);
}

function mw_api_get_logs_for_person($site, $person, $type=null, $limit=null)
{
	return mw_api_get_logs($site, $type, $limit, $person /*who*/, null /*pagename*/);
}

/* does $logtype exist at $site. useful for scanning logs of optional extension logs */
function mw_api_log_type_check($site, $logtype)
{
	/* api.php?action=paraminfo&querymodules=logevents&format=txtfm */

	$param = array('action'=>'paraminfo', 'querymodules'=>'logevents');

	$fetch = mw_api_get($site, $param);
	if($fetch === false) return null;

	$params = $fetch['paraminfo']['querymodules']['0']['parameters'];

	//set trap
	$types = null;

	foreach($params as $param)
	{
		if($param['name'] != 'type') continue;

		$types = $param['type'];
		break;
	}

	if($types == null)
	{
		//fell in trap
		//print "never found list of log types\n";
		return false;
	}

	if( in_array($logtype, $types) === false )
	{
		//not here
		//print "log type was not found here, sorry\n";
		return false;
	}

	return true;
}