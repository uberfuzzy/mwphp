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

//resolve_url('http://fuck.wikia.com');
require_once "func.safe_load.php";

function resolve_url($url_i, $read_cache=true, $write_cache=true)
{
	if($url_i == '')
	{
		return false;
	}
	
	/*******************************************************/
	//ok, i /mostly/ trust that its a url at this point.
	
	$hash = md5($url_i);
	//print "hash [$hash]\n";

		
	//load if file exists, create new if not
	//ALWAYS load it, even if your not going to use it (or /cry)
	global $resolve_cache;
	
	if(is_array($resolve_cache) )
	{
		//have in global, dont reload?
	}
	else
	{
		$resolve_cache = safe_load('resolve.cache');
	}
		
	//most of the time, attempt to use the cache
	if( $read_cache )
	{
		//print "using cache read\n";
		//check if we loaded some data or not
		if( count($resolve_cache) )
		{
			//does the hash exist in the array already?
			if( array_key_exists($hash, $resolve_cache) )
			{
				if($resolve_cache[$hash] != null)
				{
					//print "found in cache\n";
					return $resolve_cache[$hash];
				}
			}
		}
	}

	//ok, its new, lets do the dirty work
	
	// create a new curl isntance;
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url_i);
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// execute anc store return
	$ret = curl_exec($ch);
	
	// get the data of the fetch
	$cgi = curl_getinfo($ch);
	//print_r($cgi);
	
	// close cURL resource, and free up system resources
	curl_close($ch);
	
	// extract the final url
	$url_o = $cgi['url'];
	//print "got final url [$url_o]\n";
	/***********************************************/

	if($write_cache)
	{
		//print "using cache write\n";
		// store the final url in the array, hash of original url as key
		$resolve_cache[$hash] = $url_o;
		
		//write to disk
		safe_save( 'resolve.cache', $resolve_cache );
	}
	/***********************************************/
	
	//return the final endpoint url
	return($url_o);
}

function resolve_url_uncache($url)
{
	$resolve_cache = safe_load('resolve.cache');
	$hash = md5($url_i);

	if( array_key_exists($hash, $resolve_cache) )
	{
		unset($resolve_cache[$hash]);
	}

	safe_save( 'resolve.cache', $resolve_cache );
}

?>