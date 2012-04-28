<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
*/

require_once "mw_api_get.php";
#global $DEFAULT_CATEGORY_DEBUG_LEVEL;
// #$DEFAULT_CATEGORY_DEBUG_LEVEL = 1;

class MWCategory
{
	private $_param;
	private $_debug;
	private $_namespace;
	
	private $_uselogin;
	/*********************************************************************/

	function __construct($site=null, $cat=null, $ns=null, $continue=null)
	{
		// start debug setup --------------------------------------------
		// init to NO debug
		$this->_debug = 0;
		
		//check for magic global default
		global $DEFAULT_CATEGORY_DEBUG_LEVEL;
		if( !empty($DEFAULT_CATEGORY_DEBUG_LEVEL) )
		{
			//found, use (only useful for doing object create debugging)
			$this->_debug = $DEFAULT_CATEGORY_DEBUG_LEVEL;
		}
		// end debug setup  --------------------------------------------
		
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);

		//seed some stuff
		$this->_param = MWCategory::base_params();
		$this->_uselogin = false;
		$this->_site = null;
		$this->_return_subcat = 1;

		//process passed params, all of which are optional
		//ALWAYS USED SETTER FUNCTIONS, NEVER DIRECT SET
		if( $site != null )
		{
			$this->set_site($site);
		}
		
		if($cat != null)
		{
			$this->set_category($cat);
		}
		
		if( $ns !=null && is_integer($ns) )
		{
			$this->set_namespace($ns);
		}
		
		if( $ns != null )
		{
			$this->set_continue($continue);
		}

		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}
	
	/*********************************************************************/
	// sets or returns debug level
	public function debug($level=null)
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		if( $level === null )
		{
			//no passed, return current
			return $this->_debug;
		}
		else
		{
			//passed value, set it
			$this->_debug = $level;
		}
		$this->debugPrint( "<EXIT " . __METHOD__ . "\n", 1);
	}

	//static func to debug print
	public function debugPrint($strng, $minLevel=1)
	{
		if( $this->_debug < $minLevel) return;
		print $strng;
	}

	/*********************************************************************/
	//static class function to get base api params
	
	static function base_params()
	{
		$par = array();
		$par['action'] = 'query';
		$par['list'] = 'categorymembers';
		$par['cmlimit'] = 100;
		$par['cmprop'] = 'title|sortkey|timestamp';
		return $par;
	}

	public function set_site($new_site)
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->_site = $new_site;
		$this->detectCanonical();
		$this->BufferInit();
		$this->_done = false;
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}

	public function set_category($new_category, $init=true)
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->_category = ucfirst(trim($new_category));
		
		if( !empty($this->_prefix) )
		{
			//we know the NS_CATEGORY for this site
			$this->_param['cmtitle'] = $this->_prefix . ':' . $this->_category;
		}
		else
		{
			//we dont know the namespace's text yet, try using old style
			$this->_param['cmcategory'] = $this->_category;
		}
		
		//if category changes, that means anything we knew is usless
		if( !empty($init) )
		{
			$this->bufferInit();
		}

		$this->_done = false;
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}
		
	public function set_namespace($new_namespace)
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->_param['cmnamespace'] = $new_namespace;
		$this->_done = false;
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}
	
	function set_continue($new_continue)
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		
		if($new_continue === false)
		{
			//passed a false, this means "no more continue", remove from param
			unset($this->_param['cmcontinue']);
		}
		else
		{
			//was not a FALSE, so store continue point in param array
			$this->_param['cmcontinue'] = $new_continue;
		}
		
		//we changed the params, so done bit is no longer valid
		$this->_done = false;
		
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}
	
	/*********************************************************************/
	//gets and returns 1 pagename from the category (filling the buffer as needed)
	//returns false when a problem or when done.
	public function getone( $full=false )
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		
		// if( $this->_done )
		// {
			// $this->debugPrint( "detected done bit, we're done here\n", 2);
			# done bit set, we KNOW we're done already
			// $this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			// return false;
		// }

		//check to see if we have stuff in the buff already
		if( $buff_count = $this->bufferCount() )
		{
			//we have items still, just use those
			$this->debugPrint( "buffer still has [{$buff_count}] items, no need to fill\n", 2);
		}
		elseif( $this->_done == false )
		{
			$this->debugPrint( "buffer empty, need to fill, done=[". (int)$this->_done ."]\n", 2);
			//the buff is emtpy, ned to fill it
			$filled = $this->bufferFill();
			
			if($filled)
			{
				$this->debugPrint( "fill successful, now have [{$filled}]\n", 2);
				//the buffer now has items, anything we need to do?
			}
			else
			{
				$this->debugPrint( "fill failed, mark done, return false\n", 2);
				//no items in buffer after fill
				$this->_done = true;
				
				$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
				return false;
			}
		}
		else
		{
			$this->debugPrint( "i assume if you are here, buffer was empty, but done bit was set, so no fill\n", 2);
			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}
		
		//ok, if you are at this point, buffer has items (either from last fill, or one JUST done)
		//you are safe to pop top and return
		
		
		$this->debugPrint( "shifting from top of buffer\n", 2);
		$top = array_shift($this->_buffer);
		$this->debugPrint( "shifted {$top['title']} from top, buff now @ [". $this->bufferCount() . "]\n", 2);
		
		if($full==false)
		{
			//called with full=fall, return title 
			$top = $top['title'];
		}
		else
		{
			//full=true, use who thing
			//$top = $top;
		}

		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
		return $top;
	}
	
	/*********************************************************************/
	public function bufferInit()
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->bufferEmpty();

		if( !empty($this->_param) )
		{
			unset($this->_param['cmcontinue']); #and the continue point
		}

		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}

	public function bufferEmpty()
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->_buffer = array(); #reset buffer
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
	}
	
	public function bufferFill()
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		
		if( empty($this->_site) ){
			$this->debugPrint( "empty site, no fill for you\n", 2);
			$this->_done = true; //mark as true, will get cleared when a site is set
			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}

		if( empty($this->_category) ){
			$this->debugPrint( "empty category, dur\n", 2);
			$this->_done = true; //mark as true, will get cleared when cat is changed
			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}

		$this->debugPrint( "calling api_get()\n", 2);
		$fetch = mw_api_get($this->_site, $this->_param, $this->_uselogin);
		
		if( is_array($fetch) && array_key_exists('query', $fetch) )
		{
			$this->debugPrint( "found query in fetch\n", 2);
			if( array_key_exists('categorymembers', $fetch['query']) )
			{
				$this->debugPrint( "found categorymembers in fetch[q]\n", 2);
				if( is_array( $fetch['query']['categorymembers'] ) )
				{
					$this->debugPrint( "fetch[q][cm] in an array\n", 2);
					if( count($fetch['query']['categorymembers']) > 0)
					{
						$this->debugPrint( "fetch[q][cm] has >0 items\n", 2);

						$this->_buffer = $fetch['query']['categorymembers']; //copy
						$buff_c = count($this->_buffer);
						$this->debugPrint( "_buffer now has [" . $buff_c . "] items\n", 2);
						
						
						if( array_key_exists('query-continue', $fetch) )
						{
							$cmcontinue = $fetch['query-continue']['categorymembers']['cmcontinue'];
							$this->debugPrint( "found continue data [{$cmcontinue}]\n", 2); 
							$this->set_continue($cmcontinue);
						}
						else
						{
							$this->debugPrint( "no continue data found in fetch, set stop bit\n" , 2); 
							//no continue, so wont be able get more, so set stop bit now.
							$this->set_continue(false);
							$this->_done = true;
						}
						
						$this->debugPrint( "return count of buffer [{$buff_c}], since buffer was filled ok!\n", 2);
						$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
						return $buff_c;
					}
					else
					{
						$this->debugPrint( "found 0 cat members, buffer=null, done=true\n", 2);
						global $LAST_GET_URL;
						$this->debugPrint("LGU=[{$LAST_GET_URL}]\n", 2);
						//count( cat mem ) was 0
						$this->_buffer = null;
						$this->_done = true;

						$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
						return false;
					}
					exit("fatal:" . __LINE__ . "\n");
				}
				else
				{
					$this->debugPrint( "cat mem was not array, wtf?, buffer=null, done=true\n", 2);
					//category members was not an array? wtf
					$this->_buffer = null;
					$this->_done = true;

					$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
					return false;
				}
					exit("fatal:" . __LINE__ . "\n");
			}
			else
			{
				$this->debugPrint( "cant find catmem in query, wtf?, buffer=null, done=true\n", 2);
				//cannot find categorymembers in query? wtf
				$this->_buffer = null;
				$this->_done = true;

				$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
				return false;
			}
					exit("fatal:" . __LINE__ . "\n");
		}
		else
		{
			$this->debugPrint( "cant find query in fetch, wtf?, buffer=null, done=true, also dumping fetch\n", 2);
			//cannot find query in fetch, huh? likely an api fetching problem
			$this->_buffer = null;
			$this->_done = true;
			$this->debugPrint( print_r($fetch,true), 2 );

			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}
		
		exit("fatal:" . __LINE__ . "\n");
	}

	public function bufferCount()
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$this->debugPrint( "count = " . count($this->_buffer) . "\n", 1);
		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
		return count($this->_buffer);
	}

	/*********************************************************************/
	private function detectCanonical()
	{
		$this->debugPrint( ">ENTER " . __METHOD__ . "\n", 1);
		$nsparams = array();
		$nsparams['action'] = 'query';
		$nsparams['meta'] = 'siteinfo';
		$nsparams['siprop'] = 'namespaces';

		$fetch = mw_api_get($this->_site, $nsparams);

		//check for basic false return from api_get
		if( $fetch === false ) { 
			$this->debugPrint( "fetch false\n", 2);
			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}

		//check for expected structure.
		if( array_key_exists('query', $fetch) === false ||
		    array_key_exists('namespaces', $fetch['query']) === false )
		{
			$this->debugPrint( "something bad in query structure\n", 2);
			$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
			return false;
		}

		//by here, we know the return is a certain way,
		//so just keep what we need
		$this->_prefix = $fetch['query']['namespaces']['14']['*'];
		
		$this->debugPrint( "found prefix [" . $this->_prefix . "]\n", 1);
		unset($fetch);

		$this->debugPrint( "<EXIT " . __METHOD__ . "@" . __LINE__ . "\n", 1);
		return true;
	}
	
	public function getUrl()
	{
		if( empty($this->_site) ) return false;
		if( empty($this->_prefix) ) return false;
		if( empty($this->_category) ) return false;
		
		//poor man's version, will make 'smart' later
		#$url = "http://" . $this->_site . "/wiki/" . $this-_prefix . ':' . $this->_category;
		$url = "http://" . $this->_site;
		$url .= "/wiki/";
		$url .= $this->_prefix . ':' . $this->_category;
		return $url;
	}
}

/*
* list=categorymembers (cm) *
  List all pages in a given category
Parameters:
  cmtitle        - Which category to enumerate (required). Must include Category: prefix
  cmcategory     - DEPRECATED. Like title, but without the Category: prefix.
  cmprop         - What pieces of information to include
                   Values (separate with '|'): ids, title, sortkey, timestamp
                   Default: ids|title
  cmnamespace    - Only include pages in these namespaces
                   Values (separate with '|'): 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 100, 101, 102, 103, 110, 111
  cmcontinue     - For large categories, give the value retured from previous query
  cmlimit        - The maximum number of pages to return.
                   No more than 500 (5000 for bots) allowed.
                   Default: 10
  cmsort         - Property to sort by
                   One value: sortkey, timestamp
                   Default: sortkey
  cmdir          - In which direction to sort
                   One value: asc, desc
                   Default: asc
  cmstart        - Timestamp to start listing from
  cmend          - Timestamp to end listing at
Examples:
  Get first 10 pages in [[Category:Physics]]:
    api.php?action=query&list=categorymembers&cmtitle=Category:Physics

*/

include "cat2_test.php";