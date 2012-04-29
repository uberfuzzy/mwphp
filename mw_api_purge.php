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
