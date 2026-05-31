<?php
//		Freewheeling Easy Mapping Application
//		A collection of routines for display of trail maps and amenities
//  	copyright Roy R Weil 2019 - https://royweil.com
/*
Plugin Name: Dinomitedays
Plugin URI:  https://plugins.RoyWeil.com/dinomitedays/
Description: clean up original dinomitedays.org, add new features
Author:      Roy Weil
Author URI:  https://RoyWeil.com
Donate URI: https://plugins.royweil.com/donate
Requires at least: 4.6.1
Tested up to: 6.1.3
Depends: rrw-utilities-common
Depends: rrw_parser
License: private
Version: 1.2.50
Text Domain: dinomitedays
Domain Path: /lang
*/
ini_set("display_errors", true);
error_reporting(E_ALL);


// the following are in the ww-include directory
require_once "rrw_util_inc.php";
require_once "freewheelingeasy-wpdpExtra.php";
require_once "rrwFormat.php";
require_once "rrwParam.php";
require_once "display_tables_class.php";
// the following are in the local directory
require_once "database.php";
//require_once "dinomitedays-database.php";

require_once "build-dino-html.php";
require_once "menu_dino.php";
require_once "dinomitedays-email-photo.php";
require_once "dinomitedays-fix.php";
//require_once "dinomitedays-format.php";
require_once "dinomitedays-homeFlex.php";
require_once "dinomitedays-header-block.php";
require_once "dinomitedays-infor-block.php";
require_once "dinomitedays-make-htm.php";
require_once "dinomitedays-menu.php";
require_once "dinomitedays-misc-pages.php";
require_once "dinomitedays-page-content.php";
require_once "dinomitedays-print.php";
require_once "dinomitedays-tracker.php";
require_once "dinomitedays-upload.php";
require_once "DisplayPhotographers.php";
require_once "DisplayThumbnails.php";
//

global $eol, $errorBeg, $errorEnd;
$eol = "<br />\n";
$errorBeg = "$eol<span style='color:red' >";
$errorEnd = "</span> $eol";
global $wpdbExtra, $rrw_photographers;
$rrw_photographers = "wpprj_0photographers";
$wpdbExtra = new wpdbExtra;


add_shortcode("dinomitedays-build-dino", array("BuildDinoHtml", "generateHtml"));
add_shortcode("dinomitedays-city", array("dinomitedays_misc_pages", "ListByCity"));
add_shortcode('dinomitedays-database', array("dinomitedays_database", "displayDatabase"));
add_shortcode('dinomitedays-email', array("dinomitedays_email_photo", "uploadEmail"));
add_shortcode('dinomitedays-fix', array("dinomitedays_fix", "fix"));
add_shortcode('dinomitedays-feedback', array("dinomitedays_misc_pages", "feedback"));
add_shortcode("dinomitedays-information-block", array("dinomitedays_information_block", "doUpdate"));
add_shortcode('dinomitedays-last-seen', array("dinomitedays_misc_pages", "knownLocation"));
add_shortcode('dinomitedays-header-block', array("dinomitedays_header_block", "createHeaderBlock"));
add_shortcode('dinomitedays-homeOriginal', array("homeOriginal", "display"));
add_shortcode('dinomitedays-known-locations', array("dinomitedays_misc_pages", "knownLocation"));
add_shortcode('dinomitedays-menu', array("Dinomitedays_menu", "Display"));
add_shortcode("dinomitedays-neighborhood", array("dinomitedays_misc_pages", "neighborhood"));
add_shortcode('dinomitedays_page_content', array("dinomitedays_Page_Content", "render_page_content"));
add_shortcode('freewheeling-panorama-missing', array('PanoramaUpdate', 'streetViewMissing'));
add_shortcode("dinomitedays-print", array("dinomitedays_print", "print"));
add_shortcode('dinomitedays-process-upload', array("dinomitedays_upload", "process_upload"));
add_shortcode('dinomitedays-photographers', array("DisplayPhotographers", "Display"));
//add_shortcode("dinomitedays-show-page", array("BuildDinoHtml", "displayHtml"));
add_shortcode("dinomitedays-tracker", array("dinomitedays_tracker", "tracker"));
add_shortcode("dinomitedays-museums-store", array("dinomitedays_misc_pages", "museumsStore"));
add_shortcode('dinomitedays-streetViewUpdate', array("dinomitedays_database", "updateStreetViewField"));
add_shortcode('dinomitedays-streetViewMissing', array("dinomitedays_database", "updateStreetViewField"));
add_shortcode('dinomitedays-thumbnails', array("DisplayThumbnails", "display"));
add_shortcode('dinomitedays-upload', array("dinomitedays_upload", "upload"));

// 	*******************************  check for updates to the roy-header theme
require 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://pluginserver.royweil.com/dinomitedays.php',
    __FILE__,
    'dinomitedays'
);
