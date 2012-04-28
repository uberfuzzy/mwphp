<?php

#include "mw_api_cat2.php";
$test = 0;
#global $DEFAULT_CATEGORY_DEBUG_LEVEL;
#$DEFAULT_CATEGORY_DEBUG_LEVEL = 2;

// print "-------------------[".$test++."]-------------------\n";
// $foo = new MWCategory();
// $foo->set_site('fi.wikia.com');
// unset($foo);

// print "-------------------[".$test++."]-------------------\n";
// $foo = new MWCategory('community.wikia.com');
// $foo->set_category('Browse');
// print $foo->getUrl();
// unset($foo);

print "-------------------[".$test++."]-------------------\n";
$foo = new MWCategory('community.wikia.com', 'Browse');
$foo->debug(0);

while( $thing = $foo->getOne(1) )
{
	print "----- IN USER SPACE, TOP OF LOOP -----\n";
	#if($thing['ns'] == 14) continue;
	// print $thing . "\n";
	print_r( $thing ); print "\n";
	print "----- IN USER SPACE, END OF LOOP -----\n";
}
unset($foo);

// print "-------------------[".$test++."]-------------------\n";
// $foo = new MWCategory('community.wikia.com', 'Browse', 6);
// unset($foo);

// print "-------------------[".$test++."]-------------------\n";
// $foo = new MWCategory('community.wikia.com', 'Browse', 6, 'U');
// unset($foo);
