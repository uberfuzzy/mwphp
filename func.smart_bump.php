<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * E_NOTICE safe way to bump/bump_create an array key
 */

function smart_bump(&$aray, $indx, $bump=1)
{
	if(empty($aray[$indx]))
	{
		$aray[$indx] = $bump;
	}
	else
	{
		$aray[$indx] += $bump;
	}
}
