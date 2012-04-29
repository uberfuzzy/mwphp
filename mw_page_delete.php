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

/***************************************************
 * DEFUNCT CODE!!! DO NOT USE. CONVERT TO API ASAP *
 ***************************************************/

require_once "mw_post.php";
require_once "mw_api_token.php";

function mw_page_delete($host, $page, $summary)
{
	/** get a delete token **/
	$token = mw_api_get_token($host, $page, 'delete');
	
	if( $token == false )
	{
		print "error getting delete token\n";
		return false;
	}
	
	/** ok, we have a delete token **/
	
	/*
	action=delete
	wpEditToken=$token
	wpReason="clearing old default page to allow new format"
	*/

/**********************************************************/
	$payload = array();
	$payload['wpEditToken'] = $token;
	$payload['wpReason'] = $summary;

	$url = "http://" . $host . "/index.php?action=delete&title=" . urlencode($page);
	//print_r($url); print "\n";
	$raw = mw_post($url, $payload, true);
	
	//file_put_contents("cache/{$host}.html", $raw);
	if( strpos( $raw, "Internal error" ) !== false)
	{
		return false;
	}
	
	return true;
/**********************************************************/	
}

?>