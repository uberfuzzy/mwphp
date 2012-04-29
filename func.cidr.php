<?php
/*
this function preforms CIDR range matching

written by users on stackoverflow.com, slightly tweaked.
http://stackoverflow.com/questions/594112

use examples:
var_dump( cidr_match('127.1.2.3', '127.0.0.0/8') ); // match first left quad
var_dump( cidr_match('127.1.2.3', '127.0.0.0/16') ); // match left 2
var_dump( cidr_match('127.1.2.3', '127.0.0.0/24') ); // match left 3
var_dump( cidr_match('127.1.2.3', '127.0.0.0/32') ); // match all 4?
*/

function cidr_match($ip, $range)
{
	list ($subnet, $bits) = split('/', $range);
	$ip = ip2long($ip);
	$subnet = ip2long($subnet);
	$mask = -1 << (32 - $bits);
	$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
	return ($ip & $mask) == $subnet;
}
