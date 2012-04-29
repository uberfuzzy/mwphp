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

require_once "mw_post.php"; // for the post
require_once "mw_api_token.php"; // to get the edit token
require_once "mw_api_getpage.php"; // to get the last edit timestamp of the page (edit conflict crap)

/*
note:
does not have section edit support(yet)
does not have new section support (yet)
all edits marked as minor
*/

function mw_page_save($site, $pagename, $text, $summary)
{
	//print "in " . __METHOD__ . "\n";
	
	$token = mw_api_get_token($site, $pagename, 'edit');
	if($token == false)
	{
		//print "token fail\n";
		return false;
	}
	
	$payload = array();
	$payload['action'] = 'edit';
	$payload['title'] = $pagename;
		
	$payload['wpTextbox1'] = $text;
	$payload['wpSummary']  = $summary;
	$payload['wpEditToken'] = $token;
	$payload['wpMinoredit'] = 'on';

	$wpTime = gmdate('YmdHis');
	$payload['wpStarttime'] = $wpTime;
	$payload['wpEdittime'] = $wpTime;

	$lastedit = mw_api_get_page_timestamp($site, $pagename);
	//print "le [$lastedit]\n";
	$payload['wpEdittime'] = gmdate('YmdHis', strtotime($lastedit));

	// print "-----------------------------------\n";
	// print "payload ready\n";
	// print_r($payload);
	// print "-----------------------------------\n";
	
	$url = "http://{$site}/index.php";
	// print "url [$url]\n";
	
	// print "-----------writing-----------------\n";
	$raw = mw_post($url, $payload, true);
	// print "-----------------------------------\n";
	
	// print "ret = " . ( ($raw)?('true'):('false') ) . "\n";
	// print "\n\n";
	
	// file_put_contents('raw.html', $raw);
	// global $LAST_CURL_CGI;
	// print_r($LAST_CURL_CGI);
	
	sleep(1);
	return true;
}
