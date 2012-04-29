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

function mw_rollback($site, $title, $vandal, $summary)
{
	print "*** mw_ROLLBACK *** \n";
	/*
    * title: The page you want to rollback.
    * token: The token obtained in the previous request. Take care to urlencode the '+' as '%2B'.
    * user: The author of the last revision.
    * summary: Edit summary (optional). If not set, a default summary will be used.
    * markbot: If set, both the rollback and the revisions being rolled back will be marked as bot edits.
	*/

	$token = mw_api_get_rollbacktoken($site, $title);
	print "got token = "; var_dump($token); print "\n";
	
	if($token == false)
	{
		print "bad token\n";
		return false;
	}
	
	/*
	title=Main_Page
	&action=rollback
	&from=Thorn93
	&token=b8c3049a9a4fc64ac22aceb01b5bf84f%2B%5C
	&bot=1
	*/
	
	$params = array(
		'action' => 'rollback',
	
		'title' => $title,
		'token' => $token,
		'from' => $vandal,
		'summary' => $summary,
		'bot' => 1,
	);

	print "params=";
	print_r($params);
	
	
	$url = 'http://' . $site . '/index.php';
	print "post url=[{$url}]\n";
	
	
	print "doing post...";
	$ret = mw_post($url, $params, true);
	print "done\n";
	
	if($ret == false)
	{
		print "ret was false! for [{$title}]\n";
		return false;
	}
	
	print "ret=" . strlen($ret) . " bytes\n";
	
	$fn = 'tmp/post_ret-'. urlencode($title) .'.html';
	print "cacheing to disk [{$fn}]...";
	file_put_contents($fn, $ret);
	print "done.\n";
	
	if( strpos($ret, 'Permissions Errors') )
	{
		print "found 'Permissions Errors' in text, likely failed post\n";
	}
}

