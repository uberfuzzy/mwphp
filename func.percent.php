<?php
/*
 * pretty wrapper around math and number_format.
 */

function make_percent($top, $bottom, $places=0)
{
	$p = ($top/$bottom) * 100;

	return number_format($p, $places);
}
