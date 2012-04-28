<?php
/******************************************************************************
 * code copyright 2008-2010 Chris L. Stafford, all rights reserved            *
 * for private use only.                                                      *
 * limited use/viewing granted for current employees of Wikia Inc.            *
 * code is maintained in a private svn, and subject to being changed at will. *
 * do not modify without permission                                           *
 ******************************************************************************/

/*
 * including this file will create the same DEFINE namespace vars that MW has
 */
 
define('NS_MEDIA', -2);
define('NS_SPECIAL', -1);

define('NS_MAIN', 0);
define('NS_TALK', 1);

define('NS_USER', 2);
define('NS_USER_TALK', 3);

define('NS_PROJECT', 4);
define('NS_PROJECT_TALK', 5);

define('NS_FILE', 6);
define('NS_FILE_TALK', 7);

define('NS_MEDIAWIKI', 8);
define('NS_MEDIAWIKI_TALK', 9);

define('NS_TEMPLATE', 10);
define('NS_TEMPLATE_TALK', 11);

define('NS_HELP', 12);
define('NS_HELP_TALK', 13);

define('NS_CATEGORY', 14);
define('NS_CATEGORY_TALK', 15);

define('NS_IMAGE', NS_FILE);
define('NS_IMAGE_TALK', NS_FILE_TALK);
