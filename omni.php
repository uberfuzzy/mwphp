<?php
/*
	This was a master I AM LAZY method to include the ENTIRE library at once.
	This was NOT recommended (it defeats the point of the design of the library),
	but was available for very very fast rapid bot development.
	
	It is suggested that if you use this, once your code stablizes and is "done",
	you use dump out get_included_files() from every path in your code,
	sort+unique the list, and only include those.
*/

include_once "mw_api_get.php";
include_once "mw_api_post.php";

include_once "mw_ns_constants.php";

include_once "ez_login.php";
include_once "func.cidr.php";
include_once "func.get_raw.php";
include_once "func.mw_urlencode.php";
include_once "func.safe_load.php";
include_once "mw_api_cat.php";
include_once "mw_api_delete.php";
include_once "mw_api_exist.php";
include_once "mw_api_getpage.php";
include_once "mw_api_get_backlinks.php";
include_once "mw_api_get_group.php";
include_once "mw_api_get_logs.php";
include_once "mw_api_get_message.php";
include_once "mw_api_get_namespace.php";
include_once "mw_api_get_prefix.php";
include_once "mw_api_get_siteinfo.php";
include_once "mw_api_login.php";
include_once "mw_api_move.php";
include_once "mw_api_protection.php";
include_once "mw_api_purge.php";
include_once "mw_api_redirect.php";
include_once "mw_api_rollback.php";
include_once "mw_api_save.php";
include_once "mw_api_token.php";
//include_once "mw_api_undelete.php"; //split to delete and token
include_once "mw_extract_vars.php";
//include_once "mw_page_delete.php";
//include_once "mw_page_move.php";
//include_once "mw_page_rollback.php";
//include_once "mw_page_save.php";
include_once "mw_post.php"; //has to stay (until 1.16), used by _rights and _upload
include_once "mw_rights.php";
include_once "mw_upload.php";
include_once "mw_url2site.php";
include_once "mw_whoami.php";
//include_once "resolve_url.php"; //2 is better
include_once "resolve_url_2.php";
include_once "TwitterPost.php";
include_once "wikia.citylist.php";
include_once "mw_api_watch.php";
