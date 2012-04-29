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

/**
	function:
		mw_api_get_token

	desc:
		gets an token needed for almost all non-read actions.

	inputs:
		SITE [string]
			api SITE string
		PAGENAME [string]
			name of the page you are getting a token against
		TOKEN_TYPE [string]
			valid token types: edit, delete, protect, move, block, unblock

	returns:
		[false]
			problem getting fetching api?
		[string]
			valid edit token

	notes:
		-will call api_get with login=true (duh), so login first to generate cookie
		-type, not really needed currently, as "a token is a token" -#mediawiki
		-rollback tokens are generated VERY differently,
		so is in own standalone function, call that directly, or call this with type='rollback'

*/
require_once "mw_api_get.php";
require_once "mw_api_post.php";

function mw_api_get_token($site, $pagename, $token_type='edit', $uselogin=true)
{
	/* filter token type against idiots */
	switch($token_type)
	{
		case 'edit':
		case 'delete':
		case 'protect':
		case 'move':
		case 'block':
		case 'unblock':
		case 'import':
			//all valid, do nothing
			break;

		case 'rollback':
			//special case, fork to external function
			$token = mw_api_get_rollbacktoken($site, $pagename);
			return $token;
			break;
		case 'undelete':
			//special case, fork to external function
			$token = mw_api_get_undeletetoken($site, $pagename);
			return $token;
			break;

		default:
			//dont know what they tried, and dont care
			//i should throw an error and return false, but.. meh
			$token_type = 'edit';
	}

	global $LAST_TOKEN_ERROR;
	global $LAST_TOKEN_PAGE;
	global $LAST_TOKEN_WARNING;
	global $LAST_TOKEN_DEV;

	$params = array(
				 'action' => 'query',
				   'prop' => 'info',
				'intoken' => $token_type,
				 'titles' => $pagename,
				 );

	global $LAST_TOKEN_DEV;
	$data = mw_api_get($site, $params, (bool)$uselogin);
	$LAST_TOKEN_DEV = $data;

	if( is_array($data) == false)
	{
		//fetch was not an array, hmmm
		//print "false on " . __LINE__ . "\n";
		$LAST_TOKEN_ERROR = "fetch was not array (".__LINE__.")";
		return false;
	}

	if( array_key_exists('query', $data) == false)
	{
		//no query in data?
		//print "false on " . __LINE__ . "\n";
		$LAST_TOKEN_ERROR = "no query in data (".__LINE__.")";
		return false;
	}

	//check for "warnings" first
	if( array_key_exists('warnings', $data) == true )
	{
		//print "found warnings\n";
		//print_r($data['query']['warnings']);
		if($data['warnings']['info']['*'] != '')
		{
			//print "false on " . __LINE__ . "\n";
			$LAST_TOKEN_ERROR = 'Found warnings, non blank (' . __LINE__ . ')';
			$LAST_TOKEN_WARNING = $data['warnings']['info']['*'];
			return false;
		}

		//print "false on " . __LINE__ . "\n";
		$LAST_TOKEN_ERROR = 'Found warnings (' . __LINE__ . ')';
		return false;
	}

	if( array_key_exists('pages', $data['query']) == false)
	{
		//no pages? not sure if this can get hit;
		//print "false on " . __LINE__ . "\n";
		$LAST_TOKEN_ERROR = "no pages? should be unpossible (".__LINE__.")";
		return false;
	}

	//ok, here we know that $data[q][p] is array

	//check for "-1" error state
	if(array_key_exists(-1, $data['query']['pages']) )
	{
		// the dreaded -1 key exists (page not found)
		#return false;
		#var_dump($data['query']);
		#print "\n";
		#die( basename(__FILE__) . '@' . __LINE__);
		#$LAST_TOKEN_ERROR = "found -1 in pages";
		#print_r($data['query']['pages']);
		#return false;
	}

	//ok, so we have a valid page, lets pop it out it
	$page = array_shift($data['query']['pages']);
	$LAST_TOKEN_PAGE = $page;

	//page now holds an array of data like
	/*
	  [24557] => Array (
				[pageid] => 24557
				[ns] => 2
				[title] => User:Uberfuzzy
				[touched] => 2008-09-22T05:47:30Z
				[lastrevid] => 178867
				[counter] => 0
				[length] => 648
				[movetoken] => 99bccfdd835ad9a24f0c917d3f0f038e+\
			)
	*/

	//check if the token we asked for is in the array
	if( empty($page[$token_type . 'token']) )
	{
		//covers when token not in array
		//AND when its blank
		//print_r($page);
		$LAST_TOKEN_ERROR = "did not find [".($token_type . 'token')."] in page data (".__LINE__.")";
		return false;
	}

	//pull it out for easier access
	$token = $page[$token_type . 'token'];

	//fuzzy: i think we should allow get token to 'blank' edit tokens, anons might want to edit too
	// its a valid token, just not for all actions, but thats not our problem.
	/*
	if($token == '+\\') // the "+\" token
	{
		// blank EDIT token means NO EDITS FOR YOU, also, no soup
		//print "false on " . __LINE__ . "\n";
		$LAST_TOKEN_ERROR = "got a 'blank' edit token";
		return false;
	}
	*/

	//no problems, so make sure to reset error code
	$LAST_TOKEN_ERROR = null;

	//all good, give them their freaking token
	return $token;
}


/*
	function:
		mw_api_get_rollbacktoken

	desc:
		gets an uniqie token needed for rollback action

	param:
		SITE
		PAGENAME

	returns:
		-false
			problem getting fetching api?
		-string
			valid rollback token (can only be used for this 1 rollback)

	note:
		-will call api_get with login=true (duh), so login first to generate cookie
*/

function mw_api_get_rollbacktoken($site, $pagename)
{
	//print "inside " . __FUNCTION__ . "\n";
	//print_r( mw_api_get($site, array('action'=>'query', 'meta'=>'userinfo'), true) );

	$rbt_params = array(
					 'action' => 'query',
					   'prop' => 'revisions',
					'rvtoken' => 'rollback',
					 'titles' => $pagename,
					 );

	$data = mw_api_get($site, $rbt_params, true);
	//print_r( mw_api_get($site, array('action'=>'query', 'meta'=>'userinfo'), true) );

	//print "data back from api_get = "; print_r($data);

	if( is_array($data) )
	{
		if( array_key_exists('query', $data) )
		{
			//check for "warnings" first
			if( array_key_exists('warnings', $data['query']) )
			{
				//print "found warnings\n";
				//print_r($data['query']['warnings']);
				if($data['query']['warnings']['info']['*'] != '')
				{
					//print "false on " . __LINE__ . "\n";
					return false;
				}

				//print "false on " . __LINE__ . "\n";
				return false;
			}

			if( array_key_exists('pages', $data['query']) )
			{
				//ok, here we know that $data[q][p] is array

				//check for "-1" error state

				if(array_key_exists(-1, $data['query']['pages']) )
				{
					// the dreaded -1 key exists (page not found)
					// print
					//return false;
				}

				//ok, so we have a valid page, lets get it
				$page = array_shift($data['query']['pages']);

				// print "shifted page out of pages, dumping\n";
				// print_r($page);
				/*
				page looks like this
					Array (
					    [pageid] => 21
					    [ns] => 0
					    [title] => Usagi Tsukino
					    [revisions] => Array
					        (
					            [0] => Array
					                (
					                    [revid] => 3376
					                    [minor] => 
					                    [user] => Thorn93
					                    [timestamp] => 2008-11-29T03:38:39Z
					                    [comment] => Soap Operas
					                    [rollbacktoken] => 815ab32b303c1f7da6e4040eb3f1294c+\
					                )
					        )
					)
				*/

				if( !empty($page['revisions'][0]['rollbacktoken']) )
				{
					$token = $page['revisions'][0]['rollbacktoken'];
				}
				else
				{
					//no token key in page data, likely not logged in, so cant 'rollback'
					print "false on " . __LINE__ . "\n";
					return false;
				}

				if($token == '')
				{
					//blank token? really should NOT happen
					print "false on " . __LINE__ . "\n";
					return false;
				}

				if($token == '+'. chr(92)) // the "+\" token
				{
					// blank EDIT token means NO EDITS FOR YOU, also, no soup
					print "false on " . __LINE__ . "\n";
					return false;
				}

				//die('@' . __FUNCTION__ . ':' . __LINE__ . "\n");
				//all good, give them their freaking token
				return $token;
			}
			else
			{
			}

			//no pages? not sure if this can get hit;
			//print "false on " . __LINE__ . "\n";
			return false;
		}

		//no query in data?
		//print "false on " . __LINE__ . "\n";
		return false;
	}

	//fetch was not an array, hmmm
	//print "false on " . __LINE__ . "\n";
	return false;
}

/*
	function:
		mw_api_get_undeletetoken

	desc:
		fetchs a undelete token needed to get deleted revisions, or to preform undelete operations

	param:
		site [string] where
		pagename [string] what

	returns:
		false
			hard false from API_POST
		false
			no deleted revs
		string
			token

	require:
		mw_api_post

	note:
		(in isolated function because of non-standard return format)
		(former part of undelete module)
		needs work
		*proper error catching
*/

function mw_api_get_undeletetoken($site, $pagename)
{
	$param = array();
	$param['action'] = "query";
	$param['list'] = "deletedrevs";
	$param['titles'] = $pagename;
	$param['drprop'] = "token";
	$param['drlimit'] = "1";

	$fetch = mw_api_post($site, $param, true);

	//print_r ( $fetch );

	if($fetch === false) { return false; }

	$revs = $fetch['query']['deletedrevs'];

	if( count($revs) != 1 )
	{
		return false;
	}

	$token = $revs[0]['token'];

	return $token;
}
