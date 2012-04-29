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

/*
requires: (as needed)
mw_post.php
func.get_raw.php
mw_api_get

reqires by proxy:
api_login
*/

function mw_add_group($site, $user, $wanted_group, $summary='')
{
	//print "we want to add [$wanted_group] to [$user] at [$site]\n";
	
	$current_groups = api_get_user_groups($site, $user, true);
	if($current_groups === false) { return false; }
	
	//print "from="; 	print_r($current_groups);

	if( array_search($wanted_group, $current_groups) !== false )
	{
		return true;
	}

	$current_groups[] = $group;

	//print "to="; 	print_r($current_groups);
	
	mw_set_rights($site, $user, $current_groups, $summary);

	$current_groups = api_get_user_groups($site, $user, true);
	
	if( array_search($wanted_group, $current_groups) !== false )
	{
		return true;
	}
	else
	{
		return false;
	}
}

function mw_remove_group($site, $user, $unwanted_group, $summary='')
{
	//print "we want to remove [$unwanted_group] from [$user] at [$site]\n";
	
	$current_groups = api_get_user_groups($site, $user, true);
	if($current_groups === false) { return false; }

	//print "from="; 	print_r($current_groups); print "\n";
	
	$key = array_search($unwanted_group, $current_groups);
	if( $key === false )
	{
		//print "requested remove [{unwanted_group}] is already gone\n";
		return true;
	}
		unset($current_groups[$key]);

	//print "to="; print_r($current_groups); print "\n";
	
	mw_set_rights($site, $user, $current_groups, $summary);

	$current_groups = api_get_user_groups($site, $user, true);
	
	if( array_search($unwanted_group, $current_groups) === false )
	{
		return true;
	}
	else
	{
		return false;
	}
}

function mw_set_rights($site, $user, $wanted_groups, $summary='')
{
	//print "in " . __METHOD__ . "\n";
	//print "site[$site]\n";
	//print "user[$user]\n";
	//print "groups="; print_r($groups);
	
	require_once "mw_post.php";

	//-----------------------------------------------------------
	//where we send
	$post_url = "http://" . $site . "/wiki/Special:UserRights";
	
	//-----------------------------------------------------------
	//what we send
	$payload = array();
	$payload['saveusergroups'] = 'Save user groups';

	$payload['user'] = $user;
	$payload['user-reason'] = $summary; //log summary
	
	$token = get_userrights_token($site, $user);
	$payload['wpEditToken'] = $token;

	/** groups voodoo goes here
	//$payload['wpGroup-rollback'] = '1';
	**/
	foreach($wanted_groups as $g)
	{
		$payload['wpGroup-' . $g] = '1';
	}
	/*******************************************/
	//print "payload="; print_r($payload);
	/*******************************************/

	$ret = mw_post($post_url, $payload, true);

	global $LAST_API_URL, $LAST_CURL_CGI;
	global $payload_string;
	global $last_post_ret;
	$last_post_ret = $ret;

	////print "payload_string=[$payload_string]\n";
	////print "LAST_API_URL=[$LAST_API_URL]\n";
	////print "LAST_CURL_CGI="; print_r($LAST_CURL_CGI);

	// file_put_contents('raw.html', $ret);

	$current_groups = api_get_user_groups($site, $user, true);
	
	if( $current_groups === $wanted_groups )
	{
		return true;
	}
	else
	{
		return false;
	}
}

function get_userrights_token($site, $user)
{
	require_once "func.get_raw.php";
	$post_url = "http://" . $site . "/wiki/Special:UserRights";

	$po = get_raw_url($post_url . "/" . $user, true);
	$raw = $po['*'];

	////print "len()=" . strlen($raw) . "\n";
	/*
	<input name="wpEditToken" type="hidden" value="996cdfd0594410acf02e28ddcc693e07+\" />
	*/

	$pattern = '<input name="wpEditToken" type="hidden" value="([0-9a-f]{32}\+\\\)';
	$ret = ereg($pattern, $raw, $parts);

	//var_dump($ret);
	//var_dump($parts);

	$token = $parts[1];
	//die();
	////print "token="; var_dump($token);
	
	return $token;
}

function api_get_user_groups($site, $user)
{
	require_once "mw_api_get.php";
	$param = array(
		'action'=>'query',
		'list'=>'users',
		'ususers'=>$user,
		'usprop'=>'groups',
	);
	
	$fetch = mw_api_get($site, $param);

	if( $fetch === false ) { return false; }
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('users', $fetch['query']) === false ) { return false; }
	
	$fetch = $fetch['query']['users'];

	//got a user?
	if( count($fetch) == 0 ) { return false; }

	//gimme
	$fetch = array_pop($fetch);
	
	//does user have any groups?
	if( array_key_exists('groups', $fetch) === false ) { return array(); }
	
	$fetch = $fetch['groups'];
	//print_r($fetch);
	
	return $fetch;	
}

