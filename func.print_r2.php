<?php
/******************************************************************************
    Copyright 2012 Christopher L. Stafford

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
function print_rr( $input, $return=false ) {
	$lines = explode( "\n", print_r( $input, true ) );

	$lines = array_slice( $lines, 2, -3 );
	foreach( $lines as &$line ) {
		$line = substr($line, 4);
	}
	$out = implode("\n", $lines) . "\n";

	if( !empty($return) ) {
		return $out;
	} else {
		print $out;
	}
}