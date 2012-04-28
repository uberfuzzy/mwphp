<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * these functions make it easy to get google chart api images
 */


/*
 * pass in params (converted to url)
 * pass in url+true
 *
 * get back array of CGI data+image data
 */
function googleChartImage($item, $isUrl = false, $do_post=false)
{
	if( empty($isUrl) && $do_post== false)
	{
		//no flag, so they passed params, not a url, so build one
		$url = googleChartURL($item);
		if($url === false) return false;
	}
	else
	{
		$url = $item;
	}
	if($url > 3000) {
		$do_post = true;
	}
	/**********************************************************/
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);

	if( !empty($do_post) ) {
		curl_setopt($ch, CURLOPT_URL, "http://chart.apis.google.com/chart");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
	}

	//for returning
	$out = array();

	//image data (hopefully)
	$out['*'] = curl_exec($ch);
	//meta data
	$out['cgi'] = curl_getinfo($ch);

	// close cURL resource, and free up system resources
	curl_close($ch);
	unset($ch); //do i need to do this?

	if($out['cgi']['content_type'] != 'image/png')
	{
		return false;
	}

	return $out;
}

/*
 * pass in params, get back a GOOGLE url
 * (is called by googleChartImage if you pass it param array)
 */
function googleChartURL($params, $sep='&')
{
	if( empty($params) ) return false;

	$pairs = array();
	foreach($params as $key=>$val)
	{
		$pairs[] = urlencode($key) . '=' . urlencode($val);
	}
	$data = implode($sep, $pairs);
	$data = str_replace('%2C', ',', $data);
	$data = str_replace('%7C', '|', $data);

	$base = "http://chart.apis.google.com/chart?";
	$url = $base . $data;

	return $url;
}


function GoogleSimpleEncode( $valueArray, $addHeader=true )
{
	$alphaPrime = 
		'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';

	$chartData = array();
	if( $addHeader )
	{
		$chartData[] = 's:';
	}

	foreach($valueArray as $currentValue)
	{
		if ( is_numeric($currentValue) )
		{
			$currentIndex = floor($currentValue);
			
			if( $currentValue < 0)
			{
				//char will be a _
				$currentIndex = 62;
			}
			elseif( $currentValue > 61 )
			{
				//will cap it
				$currentIndex = 61;
			}
			else
			{
				//index is inside range of 0 to 61, so use it
			}
			
			$chartData[] = substr(
				$alphaPrime,
				$currentIndex,
				1
				);
		}
		else
		{
			$chartData[] = '_';
		}
	}
	return implode('', $chartData);
}

function GoogleSimpleEncodeMulti( $valueArray )
{
	$encoded = array();
	foreach($valueArray as $curArray)
	{
		$encoded[] = GoogleSimpleEncode($curArray, false);
	}
	return 's:' . implode(',', $encoded);
}