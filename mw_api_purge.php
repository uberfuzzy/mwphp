<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
api.php?action=purge&titles=Main_Page|API

use:
mw_api_purge($site, 'Pagename');
mw_api_purge($site, 'Pagename|AnotherPage');
mw_api_purge($site, array('Pagename', 'AnotherPage') );

return:
true=stuff was purged
false=blank names
array=api fail, check [code] and [info] for details

WARNING: DOES NOT PURGE IMAGES!!!
*/
require_once "mw_api_post.php";

function mw_api_purge($site, $names, $uselogin=true)
{
	global $LAST_PURGE_ERROR;
	$LAST_PURGE_ERROR = null;
	
	if( is_array($names) )
	{
		$names = implode("|", $names);
	}
	
	if($names == '')
	{
		return false;
	}
	
	$param = array('action'=>'purge', 'titles'=>$names);
	
	$fetch = mw_api_post($site, $param, $uselogin);
	
	if( !empty($fetch['error'] ) )
	{
		$LAST_PURGE_ERROR = $fetch['error'];
		return false;
	}
	
	return true;
}
