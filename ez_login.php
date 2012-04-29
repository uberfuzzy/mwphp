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
		ez_login

	desc:
		allows easy API login without placing password in source.

	params:
		site [string]
			base wiki site url
		name [string]
			the name (but not nessessary a Username) to login as,
				goes into building a FILENAME
			
	return:
		[bool,true]
			login OK!
		[bool,false]
			login failed :(
			
	require:
		mw_api_login
			mw_api_post

	notes:
		config:
			create a file called ez_login.config.php in your include path
			have this define EZ_LOGIN_PATH to point to where .store files are

		files:
			create .store files in said dir in the format of:
			ez_login.NAME.store

			NAME is the thing you pass to the ez_login function,
			does not have to be the user defined in the file (but it helps)

			.store files need to set php vars API_USER and API_PASS with creds
**/

require_once "mw_api_login.php";

function ez_login($site, $user)
{
	// no path define?
	if( !defined('EZ_LOGIN_PATH') ) {
		// try to get one
		@include "ez_login.config.php";
	}
	
	// start building filename
	$store_file = "ez_login.{$user}.store";
	
	if( defined('EZ_LOGIN_PATH') ) {
		//turn filename into filepath
		$store_file = EZ_LOGIN_PATH . $store_file;
	}

	// does that file exist?
	if( file_exists($store_file) == false )
	{
		// no file? thats bad
		return null;
	}

	// inject the u/p vars
	include $store_file;

	// attempt the login
	$ret = mw_api_login($site, $API_USER, $API_PASS);

	// cleanup (paranoid, but i dont trust garbge collection)
	unset($API_USER);
	unset($API_PASS);

	// give back what ye got.
	return $ret;
}