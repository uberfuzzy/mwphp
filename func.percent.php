<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * pretty wrapper around math and number_format.
 */

function make_percent($top, $bottom, $places=0)
{
	$p = ($top/$bottom) * 100;

	return number_format($p, $places);
}
