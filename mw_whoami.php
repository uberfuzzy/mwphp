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
