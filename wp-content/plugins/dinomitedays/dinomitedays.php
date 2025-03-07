<?php
//		Freewheeling Easy Mapping Application
//		A collection of routines for display of trail maps and amenities
//  	copyright Roy R Weil 2019 - https://royweil.com
/*
Plugin Name: Dinomitedays
Plugin URI:  https://plugins.RoyWeil.com/dinomitedays/
Description: clean up orginal dinomitedays.org, add new featutures
Author:      Roy Weil
Author URI:  https://RoyWeil.com
Donate URI: https://plugins.royweil.com/donate
Requires at least: 4.6.1
Tested up to: 6.1.3
Depends: rrw-utilities-common
Depends: rrw_parser
License: private

Version: 1.2.9

Text Domain: dinomitedays
Domain Path: /lang
*/
ini_set("display_errors", true);
error_reporting(E_ALL | E_STRICT);

// the follwoing are in the ww-include directory
require_once "rrw_util_inc.php";
require_once "freewheelingeasy-wpdpExtra.php";
require_once "rrwFormat.php";
require_once "rrwParam.php";
require_once "display_tables_class.php";

// the following are in the local directory
require_once "database.php";
require_once "dinomitedays-fix.php";
require_once "dinomitedays-header-block.php";
require_once "dinomitedays-upload.php";
require_once "dinomitedays-make-html.php";
require_once "dinomitedays-misc-pages.php";
require_once "DisplayPhotographers.php";
require_once "DisplayThumbnails.php";

global $eol, $errorBeg, $errorEnd;
$eol = "<br />\n";
$errorBeg = "$eol<span style='color:red' >";
$errorEnd = "</span> $eol";

global $wpdbExtra, $rrw_dinos, $rrw_photographers;
$rrw_photographers = "wpprj_0photographers";
$wpdbExtra = new wpdbExtra;
$rrw_dinos = "wpprrj_00rrwdinos";


add_shortcode('dinomitedaysfix', array("dinomitedys_fix", "fix"));
add_shortcode('dinomitedays-make-html', array("dinomitedys_make_html", "make_html_files"));
add_shortcode('dinomitedays-process-upload', array("dinomitedys_upload", "process_upload"));
add_shortcode('dinomitedays-upload', array("dinomitedys_upload", "upload"));
add_shortcode('dinomitedays-last-seen', array("dinomitedays_misc_pages", "last_seen"));
add_shortcode('photographers', array("DisplayPhotographers", "Display"));
add_shortcode('dinomitedays-thumbnails', array("DisplayThumbnails", "Display"));
add_shortcode('dinomitedays-photographers', array("DisplayPhotographers", "Display"));
add_shortcode('dinomitedays-header-block', array("dinomitedays_header_block", "header"));
add_shortcode('dinomitedays-database', array("dinomitedays_database", "displayDatabase"));
add_shortcode("dinomitedays-print", array("dinomitedays_print", "print"));
/* -------------------------------------  cause it to happen

require_once "../dinomitedass/plugin_update_check.php";
$MyUpdateChecker = new PluginUpdateChecker_2_0(
    'https://pluginserver.royweil.com/dinomitedays.php',
    __FILE__,
    'dinomitedays',
    1
);
*/
