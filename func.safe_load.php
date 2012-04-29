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
		safe_load

	desc:
		reads an serialized array from the disk and unwraps,
		even if it doesnt exist

	params:
		filename [string]
			filename (i assume it will take a path too)
		loaded [bool][optional][byref]
			a passed byref bit, to capture if the loading failed (2 outta 3 chance)
			
	return:
		[array]
			array of what ye asked for
		[array]
			empty array (note, this counts as the false)
			-will happen if file was not found
			-will happen if unserialize was false (not serialized data)
			
	require:
		nothing, only built in functions
**/
 
function safe_load( $filename, &$loaded=null )
{
	//check to see if its there to read
	//and CAN we read it (no, i dont know how, but we should check anyway)
	if(file_exists($filename) && is_readable($filename) )
	{
		//assume this will work
		$loaded = true;
		
		//read, and immediatly unwrap
		$arr = unserialize(file_get_contents($filename));
		
		if( $arr === false || !is_array($arr) )
		{
			//aww, we failed to unwrap
			$loaded = false;
			//make a new array to return
			$arr = array();
		}
	}
	else
	{
		//no file, or cant read it
		$loaded = false;
		//make a new array to return
		$arr = array();
	}
	
	return $arr;
}

/****************************************************************************/
/** func:
		safe_save

	desc:
		takes an array, serialize() it, try to write to disk

	params:
		filename [string]
			filename (i assume it will take a path too)
		dat [array]
			array of data to store

	return:
		[int]
			bytes written
		[false]
			could not write to file

	require:
		nothing, only built in functions

	notes:
		hate the name, keep typo'ing it
**/

function safe_save($filename, &$dat)
{
	//does it already exist?
	if( file_exists( $filename ) )
	{
		//its there, but can we write to it
		if( is_writable($filename) )
		{
			//yes! write and return bytes saved
			return file_put_contents($filename, serialize($dat));
		}
		else
		{
			//its there, but cant write to it
		}
	}
	else
	{
		//does not exist already, will return [false] or bytes
		return file_put_contents($filename, serialize($dat));
	}
	return false;
}
