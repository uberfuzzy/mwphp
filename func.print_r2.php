<?php
/******************************************************************************
    Copyright 2014 Christopher L. Stafford

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
 * Works the same as print_r, but without the leading "Array (", and trailing "), and left indent
 */
function print_rr($param, $return=false, $depth=0) {
	if($return) {
		if( (bool)ini_get('output_buffering') ) { ob_start(); }
	}
	$self = __METHOD__;
	foreach($param as $k=>$v ) {
			print str_repeat("    ", $depth);
			print "[{$k}] => ";
		if( is_array($v) ) {
			print "Array\n";
			$self($v, false, $depth+1);
		} else {
			print $v . "\n";
		}
	}
	if($return) {
		if( (bool)ini_get('output_buffering') ) { return ob_get_clean(); }
	};
}
