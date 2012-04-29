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
 * pass in a raw page get of a Mediawiki page (Wikia only?)
 * func will extract our the site/page vars exposed to the global javascript table,
 * and return an assoc array of those vars and vals
 */

/*
example

Array
(
    [skin] => oasis
    [stylepath] => http://images.wikia.com/common/__cb36434/skins
    [wgUrlProtocols] => http\\:\\/\\/|https\\:\\/\\/|ftp\\:\\/\\/|irc\\:\\/\\/|gopher\\:\\/\\/|telnet\\:\\/\\/|nntp\\:\\/\\/|worldwind\\:\\/\\/|mailto\\:|news\\:|svn\\:\\/\\/|xmpp\\:
    [wgArticlePath] => /wiki/$1
    [wgScriptPath] => 
    [wgScriptExtension] => .php
    [wgScript] => /index.php
    [wgVariantArticlePath] => false
    [wgActionPaths] => {}
    [wgServer] => http://eq2.wikia.com
    [wgCanonicalNamespace] => Project
    [wgCanonicalSpecialPageName] => false
    [wgNamespaceNumber] => 4
    [wgPageName] => EverQuest_2_Wiki:Main_Page
    [wgTitle] => Main Page
    [wgAction] => view
    [wgArticleId] => 43497
    [wgIsArticle] => true
    [wgUserName] => null
    [wgUserGroups] => null
    [wgUserLanguage] => en
    [wgContentLanguage] => en
    [wgBreakFrames] => false
    [wgCurRevisionId] => 528140
    [wgVersion] => 1.16.4
    [wgEnableAPI] => true
    [wgEnableWriteAPI] => true
    [wgSeparatorTransformTable] => ["", ""]
    [wgDigitTransformTable] => ["", ""]
    [wgMainPageTitle] => EverQuest 2 Wiki:Main Page
    [wgFormattedNamespaces] => {"-2": "Media", "-1": "Special", "0": "", "1": "Talk", "2": "User", "3": "User talk", "4": "EverQuest 2 Wiki", "5": "EverQuest 2 Wiki talk", "6": "File", "7": "File talk", "8": "MediaWiki", "9": "MediaWiki talk", "10": "Template", "11": "Template talk", "12": "Help", "13": "Help talk", "14": "Category", "15": "Category talk", "110": "Forum", "111": "Forum talk", "112": "News", "113": "News talk", "114": "Update", "115": "Update talk", "116": "Guild", "117": "Guild talk", "400": "Video", "401": "Video talk"}
    [wgNamespaceIds] => {"media": -2, "special": -1, "": 0, "talk": 1, "user": 2, "user_talk": 3, "everquest_2_wiki": 4, "everquest_2_wiki_talk": 5, "file": 6, "file_talk": 7, "mediawiki": 8, "mediawiki_talk": 9, "template": 10, "template_talk": 11, "help": 12, "help_talk": 13, "category": 14, "category_talk": 15, "forum": 110, "forum_talk": 111, "news": 112, "news_talk": 113, "update": 114, "update_talk": 115, "guild": 116, "guild_talk": 117, "video": 400, "video_talk": 401, "eq2i": 4, "eq2i_talk": 5, "image": 6, "image_talk": 7}
    [wgSiteName] => EverQuest 2 Wiki
    [wgCategories] => ["EQ2i"]
    [wgRestrictionEdit] => ["sysop"]
    [wgRestrictionMove] => ["sysop"]
    [partnerKeywords] => null
    [wgNoExternals] => false
    [wgEnableAdsInContent] => 0
    [cityShort] => gaming
    [wgEnableOpenXSPC] => true
    [wgAdDriverCookieLifetime] => 1
    [adLogicPageType] => home
    [wgDartCustomKeyValues] => age=teen;age=yadult;esrb=teen;gnre=rpg;pform=pc;sex=m;volum=I;gnre=mmo;pub=sony;dev=sony;sex=m;age=13-17;age=18-34;eth=cauc;eth=asian;kids=0-2;kids=13-17;hhi=0-30;hhi=30-60;hhi=60-100;edu=nocollege
    [wgUserShowAds] => null
    [wgTimeAgoi18n] => {"day": "a day ago", "days": "%d days ago", "hour": "an hour ago", "hours": "%d hours ago", "minute": "a minute ago", "minutes": "%d minutes ago", "seconds": "a minute ago"}
    [sassParams] => {"color-body": "#bacdd8", "color-page": "#ffffff", "color-buttons": "#006cb0", "color-links": "#006cb0", "color-header": "#3a5766", "background-image": "/skins/oasis/images/themes/oasis.png", "background-align": "center", "background-tiled": "true", "wordmark-font": ""}
    [wgAssetsManagerQuery] => /__am/%4$d/%1$s/%3$s/%2$s
    [wgCdnRootUrl] => http://images1.wikia.nocookie.net
    [wgCatId] => 2
    [wgParentCatId] => 0
    [wgCityId] => 324
    [wgID] => 324
    [wgEnableAjaxLogin] => false
    [wgDB] => eq2i
    [wgDBname] => eq2i
    [wgBlankImgUrl] => http://images1.wikia.nocookie.net/__cb36434/common/skins/common/blank.gif
    [wgPrivateTracker] => true
    [wgMainpage] => EverQuest 2 Wiki:Main Page
    [wgIsMainpage] => true
    [wgStyleVersion] => 36434
    [themename] => oasis
    [wgExtensionsPath] => http://images.wikia.com/common/__cb36434/extensions
    [wgSitename] => EverQuest 2 Wiki
    [wgMenuMore] => more...
    [wgAfterContentAndJS] => []
    [wgMWrevId] => 528651
    [wgYUIPackageURL] => http://eq2.wikia.com/static/js/yui/4a6789494bea43ad4b2d9c29081af741.js
    [wgWikiFactoryTagIds] => [1, 10, 13, 19, 20, 21, 28, 40, 41, 131, 100003, 100005, 100020, 100021, 100024, 100030, 100033, 100038, 100039, 100040, 100050, 100052, 100056, 100057, 100058, 100059, 100065, 100066, 100078, 100084, 100089, 100090, 100091, 100092, 100095]
    [wgWikiFactoryTagNames] => ["pc", "fantasy", "teens_(14-17)", "mmo", "adults_(18-35)", "adults_(35+)", "rpg", "male_(66%)", "female_(34%)", "gaming", "rpg10", "mmo10", "adinvisibletop", "adinvisiblehometop", "test", "top100", "top25gaming", "top_50_gaming", "top_75_gaming", "top_100_gaming", "vanquish_windowshade", "adss", "wow", "t9", "skyscraper", "skyscrapperwow", "tandem", "tandem_2", "blizzardholidaysiteentry", "adss_medium", "top", "pc,", "mmo,", "top_pc_mmo_rpg", "addriver"]
    [wgDisableAnonymousEditing] => false
    [wgEnableGA] => true
    [_gaq] => []
    [ls_template_ns] => Template
    [ls_file_ns] => File
    [wgOneDotURL] => http://a.wikia-beacon.com/__onedot?c=324\x26lc=en\x26lid=75\x26x=eq2i\x26y=\x26u=0\x26a=43497\x26n=4
    [wgExitstitialTitle] => Leaving EverQuest 2 Wiki
    [wgExitstitialRegister] => \x3ca href=\"#\" class=\"register\"\x3eRegister\x3c/a\x3e or \x3ca href=\"#\" class=\"login\"\x3eLogin\x3c/a\x3e to skip ads.
    [wgExitstitialButton] => Skip This Ad
    [wgCookieDomain] => .wikia.com
    [wgCookiePath] => /
    [wgAdsInterstitialsEnabled] => null
    [wgAdsInterstitialsPagesBeforeFirstAd] => 5
    [wgAdsInterstitialsPagesBetweenAds] => 8
    [ExitstitialOutboundScreen] => /index.php?title=Special:Outbound\x26f=EverQuest_2_Wiki%3AMain_Page
    [wgInterstitialPath] => http://eq2.wikia.com/index.php?title=Special:Interstitial\x26u=
    [wgReturnTo] => Main Page
    [wgReturnToQuery] => 
    [wgEnableLoginAPI] => true
    [wgPageQuery] => 
    [wgComboAjaxLogin] => true
    [wgIsLogin] => false
    [wgEnableImageLightboxExt] => true
    [wgEnableWikiaFollowedPages] => true
    [wgFollowedPagesPagerLimit] => 15
    [wgFollowedPagesPagerLimitAjax] => 600
    [wgCollectQuantcastSegments] => true
    [wgIntegrateQuantcastSegments] => true
    [fbAppId] => 91f24e0d4f81427add569e7f74d1a569
    [fbUseMarkup] => true
    [fbLogo] => true
    [fbLogoutURL] => /index.php?title=Special:UserLogout\x26returnto=EverQuest_2_Wiki:Main_Page
    [fbReturnToTitle] => EverQuest_2_Wiki:Main_Page
    [fbScriptLangCode] => en_US
    [TOCimprovementsEnabled] => 1
)

*/

function mw_extract_vars($raw)
{
	global $extract_fail;

	#declare the open/close boundry tags
	$T_open = 'var skin';
	$T_close= '</script>';

	//find the open and close tags in the page
	$A = strpos($raw, $T_open);
	if($A===false) { $extract_fail=__LINE__; return false; }
	//$A = $A + strlen($T_open) + 1; // CHANGED due to "now bug"

	$B = strpos($raw, $T_close, $A);
	if($B===false) { $extract_fail=__LINE__; return false; }
	$B = $B - 1;

	//slice out the middle
	$middle = substr($raw, $A, $B-$A);

	//blow it up into lines
	$lines = explode("\n", $middle);

	//create a blank array to hold the data
	$out = array();

	//loop each line, regex out the var/val info, store in fresh empty array
	foreach($lines as $lin)
	{
		// force-blank the capture array, to prevent loop leakage (leason learned)
		$parts = array();

		//left trim the tabs/spaces off (stupid mediawiki variance)
		$lin = ltrim($lin);
		//use preg (not ereg) to regex out the var/val pair
		$ret = preg_match("/(var )?([^=]+)[=](.*)[,;]/", $lin, $parts);

		//just in case of regex failure, loop
		//note, preg returns an INT always, not a false/int
		if( $ret == 0 )
		{
			continue;
		}

		//LAZY copy, but makes later logic code easier
		//(also if format changes, i only need to change index here)
		$var = $parts[2];
		$val = $parts[3];

		//check for quoted values;
		if( substr($val,0,1) == '"' )
		{
			//value starts with quote, its a string value,
			//strip OUTER quotes, and only store the innertext
			$val = substr($val, 1, -1);
		}

		$out[$var] = $val;
	}

	// return the array of varvals
	return $out;
}
