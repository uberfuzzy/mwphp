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
