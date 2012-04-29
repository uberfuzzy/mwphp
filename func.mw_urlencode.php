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
 * php's urlencode just isnt enough for mediawiki.
 *  this function replaces spaces with underscores BEFORE urlencoding,
 *  preventing them from being turned into %20
 */

function mw_urlencode($s)
{
	return urlencode(str_replace(" ", "_", $s) );
}

function mw_urldecode($s)
{
	return str_replace("_", " ", urldecode($s) );
}

