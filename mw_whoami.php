<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

require_once "mw_api_get.php";

/**************************************************************************
	param
		site [string]
			duh
		get_groups [bool][optional, false]
			turn on to get the array of groups you is in
		get_rights [bool][optional, false]
			turn on to get list of rights you has
		unique [bool][optional, true]
			if you get list of rights, this makes it trim out dupes
			
	note:
		sends login cookies (duh)
*/

function mw_who_am_i($site, $get_groups=false, $get_rights=false, $unique=true)
{
	//build
	$param = array(
		'action'=>'query',
		'meta'=>'userinfo',
	);
	
	$options = array();
	if($get_groups) { $options[] = 'groups'; }
	if($get_rights) { $options[] = 'rights'; }
	if( count($options) )
	{ $param['uiprop'] = implode('|', $options); }
	unset($options);

	//execute
	$fetch = mw_api_get($site, $param, true);
	
	//verify
	if($fetch === false){ return false; }
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('userinfo', $fetch['query']) === false ) { return false; }
	
	//should be good to go
	
	//simplify
	$ui = $fetch['query']['userinfo'];
	unset($fetch);
	
	if($get_rights && $unique)
	{
		$ui['rights'] = array_unique($ui['rights']);
	}
	
	return $ui;
}

/**************************************************************************
	param:
		site [string]
			duh
		username [string]
			duh
		groups [bool][optional, false]
			you can haz groups?

	return
		[null]
			api failure
		[false]
			name not person
		[string]
			if just asking for person, get back username string
		[array]
			if asked for groups,
				array(
					['name'] => 'username',
					['groups'] => array(
						[0] => 'group',
						etc...
						)
					)
			groups will always exist, even if has no groups
*/			
			
function mw_who_are_you($site, $person, $get_groups = false)
{
	$param = array();
	$param['action'] = 'query';
	$param['list'] = 'allusers';
	$param['aulimit'] = '1';
	$param['auprop'] = 'groups';
	$param['aufrom'] = $person;

	require_once "mw_api_get.php";
	$fetch = mw_api_get($site, $param);
	
	print_r($fetch);
	
	if($fetch === false){ return null; }
	
	$fetch = $fetch['query']['allusers'][0];
	
	if($fetch['name'] != $person)
	{
		return false;
	}
	
	if($get_groups == false)
	{
		return $fetch['name'];
	}
	
	if( array_key_exists('groups', $fetch) == false )
	{
		$fetch['groups'] = array();
	}
	
	return $fetch;
}
