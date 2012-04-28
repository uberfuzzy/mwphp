<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

require_once "mw_api_get.php";

/********************************************************************
Array
(
	[mainpage] => Wikia
	[base] => http://www.wikia.com/wiki/Wikia
	[sitename] => Wikia
	[generator] => MediaWiki 1.13.3
	[rev] => 5794
	[case] => first-letter
	[rights] => GFDL
	[lang] => en
	[fallback8bitEncoding] => windows-1252
	[timezone] => UTC
	[timeoffset] => 0
)
*/
function mw_api_get_siteinfo_general($site)
{
	if( empty($site) ) return false;

	$fetch = mw_api_get($site, array(
							'action' => 'query',
							'meta' => 'siteinfo',
							'siprop' => 'general',
							)
					);

	if($fetch === false) { return false; }
	if( !empty($fetch['error']) ) { return false; }
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('general', $fetch['query']) === false ) { return false; }
	
	$fetch = $fetch['query']['general'];

	return $fetch;
}

/********************************************************************
Array
(
	[pages] => 43948
	[articles] => 10239
	[views] => 617818
	[edits] => 214245
	[images] => 2551
	[users] => 1205117
	[admins] => 25
	[jobs] => 0
)
*/
function mw_api_get_siteinfo_statistics($site)
{
	if( empty($site) ) return false;

	$fetch = mw_api_get($site, array(
							'action' => 'query',
							'meta' => 'siteinfo',
							'siprop' => 'statistics',
							)
					);

	if($fetch === false) { return false; }
	if( !empty($fetch['error']) ) { return false; }
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('statistics', $fetch['query']) === false ) { return false; }
	
	$fetch = $fetch['query']['statistics'];

	return $fetch;
}

/********************************************************************
Array
(
    [wgDefaultSkin] => monaco
    [wgDefaultTheme] => sapphire
    [wgAdminSkin] => monaco-sapphire
    [wgArticlePath] => /wiki/$1
    [wgScriptPath] => 
    [wgScript] => /index.php
    [wgServer] => http://www.wikia.com
    [wgLanguageCode] => en
    [wgCityId] => 177
)

WARNING: this is a WIKIA only API addition!!!

If you need these from a non-WIKIA wiki...
get raw page, use parse variables func to get array of these and more.
*/
function mw_api_get_siteinfo_variables($site)
{
	if( empty($site) ) return false;

	$fetch = mw_api_get($site, array(
							'action' => 'query',
							'meta' => 'siteinfo',
							'siprop' => 'variables',
							)
					);

	if($fetch === false) { return false; }
	if( !empty($fetch['error']) ) { return false; }
	if( array_key_exists('query', $fetch) === false ) { return false; }
	if( array_key_exists('variables', $fetch['query']) === false ) { return false; }
	
	$fetch = $fetch['query']['variables'];

	if( is_array($fetch) )
	{
		$out = array();
		foreach($fetch as $item)
		{
			//sorry moli, i cant have an array in a list of values
			if($item['id'] == 'wgContentNamespaces'){ continue; }
			
			$out[ $item['id'] ] = $item['*'];
		}
		return $out;
	}

	return false;
}

/********************************************************************
	attempt to get all 3, not sure its going to be good, but i'm lazy
*/
function mw_api_get_siteinfo($site, $at_wikia=true)
{
	if( empty($site) ) return false;

	$data = array('general' => mw_api_get_siteinfo_general($site),
               'statistics' => mw_api_get_siteinfo_statistics($site),
                );

	if($at_wikia){
		$data['variables'] = mw_api_get_siteinfo_variables($site);
	}

	return $data;
}
