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

/** func:
		mw_api_rollback

	desc:
		attempts to rollback a page to the last contributor

	params:
		site [string]
			root path, used in api calls
		title [string]
			-the pagename to rollback
		vandal [string]
			-username or IP of the person you are rolling back (IMPORTANT)
		summary [string][optional]
			default: null
			the summary used for log
			note: if left null, summary will not be sent to api,
				and the normal 'auto generated' summary will be used
		mark_bot [bool][optional]
			default: false
			flag to make this rollback edit and the vandal's edit(s) marked as bot edits, hiding both
			-requires user to have the 'markbotedits' right

	return:
		[true]
			rollback was ok!
		[false]
			error happened
			-human readable/printable error will be stored in LAST_ROLLBACK_ERROR
			-api error code will be stored in LAST_ROLLBACK_ERROR_CODE

	depend on:
		mw_api_get_token
			for getting the extremely complex rollback token
		mw_api_post
			for sending the post to api.php

	notes:
		*both token and post call will attempt to use login cookie if found.
		please login before trying to use (duh?)
		TODO: trace what a successful and unsuccessfull returns look like
*/

require_once "mw_api_post.php";
require_once "mw_api_token.php";

function mw_api_rollback($site, $title, $vandal, $summary=null, $markbot=false)
{
	global $LAST_ROLLBACK_ERROR;
	global $LAST_ROLLBACK_ERROR_CODE;
	
	$LAST_ROLLBACK_ERROR = null;
	$LAST_ROLLBACK_ERROR_CODE = null;

	$params = array(
		'action' => 'rollback',
		'title'  => $title,
		'token'  => mw_api_get_rollbacktoken($site, $title),
		'user'   => $vandal,
	);

	// user written summary? or leave off params to use auto summ
	if($summary != null)
	{
		$params['summary'] = $summary;
	}

	// set bot bit? (will be ignored if logged in user doesnt have this right)
	// QUESTION: not sure if i should leave this out of param array if false
	$params['markbot'] = (($markbot)?(1):(0));

	// post it!
	$ret = mw_api_post($site, $params, true);

	//core fail
	if($ret == false)
	{
		$LAST_ROLLBACK_ERROR = "api post return was false! for [{$title}]\n";
		$LAST_ROLLBACK_ERROR_CODE = "fzy_false_post";
		return false;
	}

	// api said it was fail, but why?
	if( !empty($ret['error']) )
	{
		$LAST_ROLLBACK_ERROR = 'found api error [' . $ret['error']['code'] . ']->['. $ret['error']['info'] .']';
		$LAST_ROLLBACK_ERROR_CODE = $ret['error']['code'];
		return false;
	}

	//post didnt false
	//api didnt choke
	//not sure what else i could check here to see if it didnt work
	//assume it did?

	return true;
}
