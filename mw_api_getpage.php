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
		mw_api_get_page
 
	desc:
		uses api_get to return the last version of the wiki text of a page
 
	param:
		site [string]
			used to build api path
		pagename [string]
			pagename of what to get gotten
		addText [bool, opt]
			default: true
			controls if page text is gotten too
 
	returns:
		[false]
			a bad api return
		[array]
			a good return! assoc array of data
				["user"]=> string() the username of the last editor
				["timestamp"]=> string() utc timestamp of last edit.
								example "2008-06-17T16:08:45Z"
				["comment"]=> string() edit summary of last edit
				["*"]=> string() current text
			

 wrappers/extractors also included are:
 -api_get_page_text
 -api_get_page_timestamp
 -mw_api_last_edit_by
*/
require_once "mw_api_get.php";

function mw_api_get_page($site, $pagename, $addText = true)
{
	$params = array();
	$params['action'] = 'query';
	$params['prop'] = 'revisions';
	$params['rvprop'] = 'user|timestamp|comment';
	
	//get and return text?
	if($addText)
	{
		$params['rvprop'] .= '|content';
	}
	
	$params['titles'] = $pagename;
	
	//get it
	$fetch = mw_api_get($site, $params);

	//hard fail?
	if($fetch === false)
	{
		return false;
	}
	
	//smart fail
	if( !empty($fetch['error']) )
	{
		//NOTE: i should catch this...
		return false;
	}

	//buh?
	if( empty($fetch['query'] ) )
	{
		return false;
	}
	
	if( empty($fetch['query']['pages']) )
	{
		//no pages, nothing can get
		return false;
	}
	
	//bad page name
	if( !empty($fetch['query']['pages']['-1']) )
	{
		//-1 in pages means there is a 'missing' key somewhere nearby
		return false;
	}

	//pop out of array
	$pag = array_shift($fetch['query']['pages']);
	
	if( array_key_exists('revisions', $pag) )
	{
		$rev = $pag['revisions'][0];
		$rev['pageid'] = $pag['pageid'];
		if( array_key_exists('comment', $rev) === false) $rev['comment'] = null;
		
		return $rev;
	}
	
	//wow this function is badly written;
	return false;
}

function mw_api_get_page_text($site, $pagename)
{
	$p = mw_api_get_page($site, $pagename, true);
	if($p == false) return false;

	return $p['*'];
}

function mw_api_get_page_timestamp($site, $pagename)
{
	$p = mw_api_get_page($site, $pagename, false);
	if($p == false) return false;
	
	return $p['timestamp'];
}

function mw_api_last_edit_by($site, $pagename)
{
	$p = mw_api_get_page($site, $pagename, false);
	if($p == false) return false;
	
	return $p['user'];
}
