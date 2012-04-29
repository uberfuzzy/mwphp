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

function mw_api_page_exists($site, $pagename)
{
	$param = array();
	$param['action'] = "query";
	$param['prop'] = "info";
	$param['titles'] = $pagename;

	$fetch = mw_api_get($site, $param);

	if( $fetch === false )
	{
		//bigger problem here
		return false;
	}

	if( !empty($fetch['error']) )
	{
		#print "ERROR="; print_r($fetch['error']);
		return false;
	}

	//print_r($fetch);
	if( empty($fetch['query']['pages']) )
	{
		//no pages array?
		return false;
	}

	if( array_key_exists( '-1', $fetch['query']['pages'] ) )
	{
		//found a -1 key in the pages array, meaning a 'missing' title
		return false;
	}

	return true;
}

/*
checks if an IMAGE exists at a pagename.

different then above, since an IMAGE can exist without a page,
 and thus, detecting a "page" is false detection.

similar to how an NS_IMAGE page can exist without an IMAGE.
*/
function mw_api_image_exists($site, $pagename)
{
	$param = array();
	$param['action'] = "query";
	$param['prop'] = "imageinfo";
	$param['titles'] = $pagename;

	$fetch = mw_api_get($site, $param);

	//check for hard false
	if( $fetch === false )
	{
		//bigger problem here
		return false;
	}

	//check for API error messages
	if( !empty($fetch['error']) )
	{
		print "ERROR="; print_r($fetch['error']);
		return false;
	}

	//print_r($fetch);
	if( empty($fetch['query']['pages']) )
	{
		//no pages array?
		return false;
	}

	if( array_key_exists( '-1', $fetch['query']['pages'] ) )
	{
		//found a -1 key in the pages array, meaning a 'missing' title
		return false;
	}

	$p = array_shift( $fetch['query']['pages'] );

	if( array_key_exists( 'imageinfo', $p ) === false )
	{
		//no imageinfo? then theres no file here
		return false;
	}

	return true;
}
