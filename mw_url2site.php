<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * given a passed url, get the page, extract the javascript vars, and build the proper API 'site'
 * 
 * diskfile: site_cache.ser (default, can be changed at runtime)
 * global: $u2s_cache
 * global: $u2s_cache_file
 * 
 * external dependency:
 *   array(*, cgi) = function get_raw_url($url)
 *   array/bool = function mw_extract_vars($rawtext)
 *   array = function safe_load($filename)
 * 
 * note: cache diskfile is READ on include of this file, and not read IN each function call.
 *       cache diskfile is WRITTEN with every call, only if new data was added.
 */
 
require_once "func.safe_load.php";
require_once "func.get_raw.php";
require_once "mw_extract_vars.php";

//setup the filename as a global
global $u2s_cache_file;
$u2s_cache_file = 'site_cache.ser';

function url2site_init()
{
	global $u2s_cache;
	global $u2s_cache_file;
	
	$u2s_cache = safe_load($u2s_cache_file);
}

function url2site($url, &$cache_flag=null)
{
	//print "url2site ($url)\n";
	global $u2s_cache;
	global $u2s_cache_file;
	
	$url_hash = md5($url);
	
	if(array_key_exists($url_hash,$u2s_cache))
	{
		$site = $u2s_cache[$url_hash];
		//print "CACHE HIT [$site]\n";
		$cache_flag = true;
		return $site;
	}
	else
	{
		//print "MISS\n";
		
		// 'get' the passed url
		$fetch = get_raw_url($url);
		
		//print "extract\n";
		
		// get the vars out of that raw page
		$vars = mw_extract_vars	( $fetch['*'] );

		// glue the parts back together
		$site = substr($vars['wgServer'], 7) . $vars['wgScriptPath'] . '';
		if($site === '') {
			#dont cache bad return;
			return false;
		}
		
		// add to cache
		$u2s_cache[$url_hash] = $site;
		
		// write cache to disk
		safe_save($u2s_cache_file, $u2s_cache);
		
		//print "site [$site]\n";
		$cache_flag = false;
		return $site;
	}
}

//load the global file into the global array
url2site_init();
