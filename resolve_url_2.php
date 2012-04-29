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
		resolve_url

	desc:
		uses php function to peek at headers of url,
		following location redirects, until finding 'last' url

	param:
		url [string]
			URL of where to start from

	return:
		[false]
			internal function returned false, nothing to recover
		[string]
			final destination

	require:

	to do/notes:
		add ghetto cache like parent version?
		maybe add param to return whole location array, and not pop?
**/

function resolve_url($in_url)
{
	//get some http data
	$headers = @get_headers($in_url, true);
	//print_r($headers);
	if($headers === false){ return false; }

	if( array_key_exists('Location', $headers) === false )
	{
		//no redirects happened, this IS the final
		return $in_url;
	}

	// redirects happened
	if( is_array($headers['Location']) )
	{
		//more then one, pop last.
		$out_url = array_pop($headers['Location']);
	}
	else
	{
		//just one, copy it.
		$out_url = $headers['Location'];
	}

	//throw away the rest
	unset($headers);

	//give back that last one
	return $out_url;
}
