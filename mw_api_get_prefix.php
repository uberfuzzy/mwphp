<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
* list=allpages (ap) *
  Enumerate all pages sequentially in a given namespace
Parameters:
  apfrom         - The page title to start enumerating from.
  apprefix       - Search for all page titles that begin with this value.
  apnamespace    - The namespace to enumerate.
                   One value: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 110, 111, 400, 401, 500, 501, 502, 503
                   Default: 0
  apfilterredir  - Which pages to list.
                   One value: all, redirects, nonredirects
                   Default: all
  apminsize      - Limit to pages with at least this many bytes
  apmaxsize      - Limit to pages with at most this many bytes
  apprtype       - Limit to protected pages only
                   Values (separate with '|'): edit, move
  apprlevel      - The protection level (must be used with apprtype= parameter)
                   Can be empty, or Values (separate with '|'): autoconfirmed, sysop
  apprfiltercascade - Filter protections based on cascadingness (ignored when apprtype isn't set)
                   One value: cascading, noncascading, all
                   Default: all
  aplimit        - How many total pages to return.
                   No more than 500 (5000 for bots) allowed.
                   Default: 10
  apdir          - The direction in which to list
                   One value: ascending, descending
                   Default: ascending
  apfilterlanglinks - Filter based on whether a page has langlinks
                   One value: withlanglinks, withoutlanglinks, all
                   Default: all
*/
/*
 0 - main
 2 - user
 4 - project
 6 - file
 8 - mediawiki
10 - template
12 - help
14 - category
*/

require_once "mw_api_get.php";

function mw_api_get_prefix($site, $prefix='', $ns=null, $redir='all')
{
	$param = array();
	$param['action'] = 'query';
	$param['list'] = 'allpages';
	$param['apprefix'] = $prefix;
	$param['aplimit'] = 100;
	
	if($ns != null)
	{
		$param['apnamespace'] = $ns;
	}
	
	if( $redir != null )
	{
		switch ($redir)
		{
			case 'r':
				$param['apfilterredir'] = 'redirects';
				break;

			case 'nr':
				$param['apfilterredir'] = 'nonredirects';
				$redir = '';
				break;

			case 'all':
			default:
				$param['apfilterredir'] = 'all';
				break;
		}
	}
	
	$continue = true;
	$out = array();
	
	while($continue)
	{
		$fetch = mw_api_get($site, $param);
		
		if( !empty($fetch['query-continue']['allpages']) )
		{
			$continue = $fetch['query-continue']['allpages']['apfrom'];
			$param['apfrom'] = $continue;
		}
		else
		{
			$continue = false;
		}
		
		if( !empty($fetch['query']['allpages']) )
		{
			//print "adding " . count($fetch['query']['allpages']) . " items\n";
			foreach($fetch['query']['allpages'] as $p)
			{
				$out[ $p['pageid'] ] = $p['title'];
			}
		}
		else
		{
			//print "nothing found to add this cycle\n";
		}
	}
	
	return $out;
}

//print_r(mw_api_get_prefix('uberfuzzy.wikia.com', '') );
