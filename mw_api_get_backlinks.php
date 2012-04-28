<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*************************************************************************
 * list=backlinks (bl) *
  Find all pages that link to the given page
Parameters:
  bltitle        - Title to search. If null, titles= parameter will be used instead, but will be obsolete soon.
  blcontinue     - When more results are available, use this to continue.
  blnamespace    - The namespace to enumerate.
                   Values (separate with '|'): 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 100, 101, 102, 103, 110, 111, 400, 401
  blfilterredir  - How to filter for redirects
                   One value: all, redirects, nonredirects
                   Default: all
  bllimit        - How many total pages to return. If blredirect is enabled, limit applies to each level separately.
                   No more than 500 (5000 for bots) allowed.
                   Default: 10
  blredirect     - If linking page is a redirect, find all pages that link to that redirect as well. Maximum limit is halved.
Examples:
  api.php?action=query&list=backlinks&bltitle=Main%20Page
  api.php?action=query&generator=backlinks&gbltitle=Main%20Page&prop=info
Generator:
  This module may be used as a generator
*************************************************************************/
/*
pass in page name, and get back an array of non-redirect pages in namespace 0

if api_get fucks up, will return a false;

if no links, returns empty array
*/

function mw_api_get_backlinks($site, $pagename)
{
	$param = array();
	$param['action'] = 'query';
	$param['list'] = 'backlinks';
	$param['bllimit'] = '25';
	$param['blfilterredir'] = 'nonredirects';
	$param['blnamespace'] = '0';
	$param['bltitle'] = $pagename;

	$continue = true;
	$out = array();

	while($continue)
	{
		// get with current params
		$fetch = mw_api_get($site, $param);

		// check for errors
		if($fetch === false)
		{
			//something FUBARd, need to stop NOW
			return false;
		}

		if( array_key_exists('query-continue', $fetch) )
		{
			//found continue!
			$continue = $fetch['query-continue']['backlinks']['blcontinue'];
			$param['blcontinue'] = $continue;
		}
		else
		{
			// no continue, no loop
			$continue = false;
		}

		if( !empty($fetch['query']['backlinks']) )
		{
			// have stuff, loop and save
			foreach($fetch['query']['backlinks'] as $page_o)
			{
				// only save title part of results
				$out[] = $page_o['title'];
			}
		}
		else
		{
			//no things to loop
			$continue = false;
		}
	}

	return $out;
}
