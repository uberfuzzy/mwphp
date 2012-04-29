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
	This was an early venture into the world of php OO coding.
	I had functions previously that would loop fetch an entire category of entries,
	but it would require buffering the entire array in memory, and did no support stop/resume.
	I later had to work on some very very large categories, some not in EN, and other issues.
	
	General premise was that you would just continue to pull 1 item from the object until done.
	The object would maintain its own buffer, and re-fetch when empty.
	It provided some neat utility functions for filtering by namespace, and pre-maturly stopping.
	The object also hid the magic needed for when NS6 != "Category" (like in DE)
*/

/*
$cato = new CatObject($site, $cat);

$cato = new CatObject($site, $cat, 500);
(or later call ->set_limit(500) )

//get all images in a category
$cato = new CatObject($site, $cat, null, 6);
(or later call ->set_ns_filter(6);

//if non-EN
$cato = new CatObject($site, null);
$cato->set_prefix('Kategorie');
$cato->set_cat('Bild_(Buchcover)');

//enable debug printing
$cato->debug(1);

while( $pagename = $cato->getone() )
{
}
*/

require_once "mw_api_get.php";

class CatObject
{
	var $_mysite = null;
	var $_mycat = null;
	var $_buffer = array();
	var $_done = null;
	var $_continue = null;
	var $_nsfilter = null;
	var $_myper = 100;
	var $_nsprefix = 'Category';
	var $_uselogin = false;
	private $_use_debug = false;
	
	/*********************************************************************/
	public function debug($bool)
	{
		if( $bool ) { $this->_use_debug = true; }
		
	}
	private function _debug_print($s)
	{
		if( !$this->_use_debug ) return;
		print $s;
	}
	/*********************************************************************/

	function __construct($site, $cat=null, $per=null, $ns=null)
	{
		//print "__construct\n";
		$this->_mysite = $site;
		
		//trim off CATEGORY: if the user passes it.
		if($cat != null)
		{
			$this->set_cat($cat);
		}
		
		if($per != null)
		{
			$this->set_limit($per);
		}
		
		if($ns != null)
		{
			$this->set_ns_filter($ns);
		}
	}
	
	static function base_params()
	{
		$par = array();
		$par['action'] = 'query';
		$par['list'] = 'categorymembers';
		$par['cmlimit'] = 100;
		$par['cmprop'] = 'title';
		return $par;
	}

	//a category isnt always "Category", sometimes its "Kategorie",
	//and the api needs this prefixed to the start of the name
	//make sure to set this before setting the category name, so it can be trimmed correctly
	function set_prefix($str)
	{
		$this->_nsprefix = $str;
	}
	
	//manually sets what category we're working on
	//(please route all changes to category though this)
	function set_cat($cat)
	{
			$this->_debug_print("in " . __METHOD__ . "\n");
			$this->_debug_print("input was [{$cat}]\n");
			$prefix_len = mb_strlen($this->_nsprefix);
			$this->_debug_print("current prefix is [{$this->_nsprefix}] ({$prefix_len})\n");
			
		//if left 9 chars == 'category' + ':'
		$left_part = mb_substr($cat, 0, $prefix_len + 1);
		if( mb_strtolower($left_part) == mb_strtolower($this->_nsprefix) . ":")
		{
			$this->_debug_print("trim mode\n");
			//trim off namespace
			$this->_mycat = mb_substr($cat, $prefix_len+1);
			
		}
		else
		{
			$this->_debug_print("direct mode\n");
			//no namespace detected, so just use
			$this->_mycat  = $cat;
		}
		
		$this->_debug_print("result was [{$this->_mycat}]\n");
		$this->_debug_print("EXIT:" . __METHOD__ . "\n");
	}

	//sets a api param flag to only return pages in this category in this namespace
	//pass INT, not name
	function set_ns_filter($ns)
	{
		$this->_nsfilter = (int)$ns;
	}
	
	function set_limit($str)
	{
		$str = (int)$str;
		
		//can we check for highapilimits later?
		//if you go over 500, and you dont have highapilimit, thats your problem
		if($str > 5000)
			$str = 5000;
			
		//why would you do that?
		if($str < 1)
			$str = 1;
			
		$this->_myper = (int)$str;
	}
	
	function use_login($b=true)
	{
		$this->_uselogin = (bool)$b;
	}
	
	//gets and returns 1 pagename from the category (filling the buffer as needed)
	//returns false when a problem or when done.
	function getone()
	{
		$this->_debug_print( "in " . __METHOD__ . "\n" );
		if( count($this->_buffer) == 0 )
		{
			$this->_debug_print( "count(buffer) == 0\n");
			
			//no items in the buffer
			if( $this->_done == false )
			{
				$this->_debug_print( "done == false\n");
				
				//we're not marked as done
				$this->_debug_print( "attempting to fill_buffer()\n");
				$ret = $this->fill_buffer();
				
				if($ret == false)
				{
					$this->_debug_print( "filling was false\n");
					//fill buffer failed
					$this->_done = true;
					return false;
				}
				else
				{
					if( count($this->_buffer) > 0)
					{
						$this->_debug_print( "filling was OK, return 1\n");
						$shft = array_shift($this->_buffer);
						$this->_debug_print( "returning \"". $shft['title'] ."\" c(buffer)=[". count($this->_buffer) ."] done=[" . (int)$this->_done . "] @ ".__LINE__."\n");
						return $shft['title'];
					}
					else
					{
						$this->_debug_print( "fillbuffer was ok, but buffer had 0, have to return false\n");
						return false;
					}
				}
				
				die('fatal:' . __LINE__);
			}
			else
			{
				$this->_debug_print( "buffer empty, found done==true\n" );
				//die('marker');
				return false;
			}
		}
		else
		{
			$this->_debug_print( "had some in buffer, using\n");
			//items in the buffer
			$shft = array_shift($this->_buffer);
			$this->_debug_print( "returning \"". $shft['title'] ."\" c(buffer)=[". count($this->_buffer) ."] done=[" . (int)$this->_done . "] @ ".__LINE__."\n");
			return $shft['title'];
		}
		
		die('fatal:' . __LINE__ );
	}
	
	function fill_buffer()
	{
		if( empty($this->_mysite) ){
			$this->_debug_print( "empty site, no fill for you\n");
			$this->_buffer = null;
			$this->_done = true;
		}

		$this->_debug_print( "in fill_buffer()\n");
		
		$par = CatObject::base_params();
		$par['cmlimit'] = $this->_myper;
		
		$par['cmtitle'] = $this->_nsprefix . ':' . $this->_mycat;
		
		if($this->_nsfilter !== null)
		{
			$this->_debug_print( "found a namespace setting [". $this->_nsfilter ."]\n");
			$par['cmnamespace'] = $this->_nsfilter;
		}
		
		if($this->_continue != null)
		{
			$this->_debug_print( "had a stored continue marker [". $this->_continue ."]\n");
			$par['cmcontinue'] = $this->_continue;
		}
		
		$this->_debug_print( "calling api_get()\n");
		$fetch = mw_api_get($this->_mysite, $par, $this->_uselogin);
		
		if( is_array($fetch) && array_key_exists('query', $fetch) )
		{
			$this->_debug_print( "found query in fetch\n");
			if( array_key_exists('categorymembers', $fetch['query']) )
			{
				$this->_debug_print( "found categorymembers in fetch[q]\n");
				if( is_array( $fetch['query']['categorymembers'] ) )
				{
					$this->_debug_print( "fetch[q][cm] in an array\n");
					if( count($fetch['query']['categorymembers']) > 0)
					{
						$this->_debug_print( "fetch[q][cm] has >0 items [" . count($fetch['query']['categorymembers']) . "]\n");

						$this->_debug_print( "filling _buffer with fetch[q][cm]\n");
						$cm = $fetch['query']['categorymembers'];
						$this->_debug_print( print_r($cm, true) );
						$this->_buffer = $cm;
						$this->_done = false;
						
						if( array_key_exists('query-continue', $fetch) )
						{
							//$p['gcmcontinue']
							$this->_debug_print( print_r( $fetch['query-continue'] , true) ); 
							$cont = $fetch['query-continue']['categorymembers']['cmcontinue'];
							$this->_debug_print( "found a continue marker in fetch[q-c][cm][gcm] = [". $cont ."]\n");
							$this->_continue = $cont;
						}
						else
						{
							//no continue, so dont reget more
							$this->_done = true;
							$this->_continue = null;
						}
						
						$this->_debug_print( "return true, since buffer was filled ok!\n");
						return true;
					}
					else
					{
						$this->_debug_print( "found 0 cat members, buffer=null, done=true\n");
						//count( cat mem ) was 0
						$this->_buffer = null;
						$this->_done = true;
						return false;
					}
					exit("fatal:" . __LINE__ . "\n");
				}
				else
				{
					$this->_debug_print( "cat mem was not array, wtf?, buffer=null, done=true\n");
					//category members was not an array? wtf
					$this->_buffer = null;
					$this->_done = true;
					return false;
				}
					exit("fatal:" . __LINE__ . "\n");
			}
			else
			{
				$this->_debug_print( "cant find catmem in query, wtf?, buffer=null, done=true\n");
				//cannot find categorymembers in query? wtf
				$this->_buffer = null;
				$this->_done = true;
				return false;
			}
					exit("fatal:" . __LINE__ . "\n");
		}
		else
		{
			$this->_debug_print( "cant find query in fetch, wtf?, buffer=null, done=true, also dumping fetch\n");
			//cannot find query in fetch, huh? likely an api fetching problem
			$this->_buffer = null;
			$this->_done = true;
			$this->_debug_print( print_r($fetch,true) );
			return false;
		}
		
		exit("fatal:" . __LINE__ . "\n");
	}

	
}

