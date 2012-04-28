<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/***************************************************
 * DEFUNCT CODE!!! DO NOT USE. CONVERT TO API ASAP *
 ***************************************************/

require_once "mw_post.php";
require_once "mw_api_token.php";

/*
a shitty index.php based page mover

post to: /index.php?title=Special:MovePage&action=submit
wtf

fields:
wpNewTitle	text, the new name
wpOldTitle	text, the old name
wpReason	textarea, summary
wpMovetalk	checkbox, move talk too
wpWatch	checkbox, watch page?
wpMove	submit
wpEditToken	hidden, move token


wpFixRedirects	checkbox, udpate redirects?
wpMovesubpages	checkbox, move subs too

wpConfirm	checkbox, for delete
wpDeleteAndMove	submit, (NEED) to do delete and move

*/

function mw_page_move($site, $old_name, $new_name, $summary)
{
	$url = "http://" . $site . "/index.php?title=Special:MovePage&action=submit";
	
	/****************************************************/
	$payload = array();
	$payload['wpNewTitle'] = $new_name;
	$payload['wpOldTitle'] = $old_name;
	$payload['wpReason'] = $summary;
	
	$payload['wpMove'] = '1';
	
	$token = mw_api_get_token($site, $old_name, 'move');
	if($token == false){ print "bad token\n"; return false; }
	$payload['wpEditToken'] = $token;
	
	/****************************************************/
	
	print "posting\n";
	$ret = mw_post($url, $payload, true);
	file_put_contents('raw.html', $ret);
}

/*
$site = 'communitytest.wikia.com';
$old_name = 'Banana';
$new_name = 'Orange';
$summary = 'overrite test';

include "mw_api_login.php";
if( api_login($site, 'Uberfuzzy', 'caff31ne') == false)
{
	print "login fail\n";
	return;
}
print "login ok\n";

print "calling mover\n";
mw_page_move($site, $old_name, $new_name, $summary);
*/