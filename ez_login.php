<?php
/******************************************************************************
    Copyright 2008-2012 Christopher L. Stafford

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
		files:
			create .store files format of:
				ez_login.<NAME>.store

			<NAME> is the thing you pass to the ez_login function,
			does not have to be the user defined in the file (but it helps)

			.store files are php scripts, that set vars API_USER and API_PASS

			files will be looked for in the local directory first,

			if not found, file named "ez_login.config.php" is loaded from path
			this config define()s a const named EZ_LOGIN_PATH,
			this const points to a directory of store files.
			this const must end with a path seperator.
			this const is prepended to the file name to check for file

**/

require_once "mw_api_login.php";

/**
 * ez login (not secure, just easy)
 * @param	string	$site	protocol less host of wiki (domain), passed to mw_api_login
 * @param	string	$user	"user" name used to build filename where credentials are stored,
 * 							may or may not be the actual username being authenticated.
 * @return	null|boolean	null indicates internal failure, bool value is direct return from mw_api_login
 **/
function ez_login($site, $user) {
	# build filename
	$store_file = "ez_login.{$user}.store";

	# can we find the file?
	if( !file_exists($store_file) ) {
		# do we have a path defined already?
		if( !defined('EZ_LOGIN_PATH') ) {
			# we do not, try to get one
			@include "ez_login.config.php";

			# do we have a path constant now?
			if( !defined('EZ_LOGIN_PATH') ) {
				# still no path, cannot prepend, fail state
				return null;
			}
		}

		# prepend path constant to filename
		$store_file = EZ_LOGIN_PATH . $store_file;

		# can we find our file now?
		if( !file_exists($store_file) ) {
			# no local file, no pathed file, no dice.
			return null;
		}

		# we found the file! continue on
	}

	# file exists now, load it, injecting its vars into local scope
	include $store_file;

	# attempt the login
	$ret = mw_api_login($site, $API_USER, $API_PASS);

	# cleanup (paranoid, but i dont trust garbge collection)
	unset($API_USER, $API_PASS);

	# give back what ye got from api
	return $ret;
}
