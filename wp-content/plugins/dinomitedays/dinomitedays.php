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
Tested up to: 5.4.2
Depends: rrw-utilities-common
Depends: rrw_parser
License: private

Version: 1.2

Text Domain: dinomitedays
Domain Path: /lang
*/
        ini_set( "display_errors", true );
        error_reporting( E_ALL | E_STRICT );
    global $eol, $errorBeg, $errorEnd;

require_once "../dinomitedasys/rrw_util_inc.php";
require_once "../dinomitedasys/freewheelingeasy-wpdpExtra.php";
 
 
require_once "../dinomitedasys/dinomitedays-fix.php";
require_once "../dinomitedasys/dinomitedays-upload.php";
require_once "../dinomitedasys/dinomitedays-make-html.php";
require_once "../dinomitedasys/dinomitedays-misc-pages.php";

add_shortcode( 'dinomitedaysfix', array("dinomitedys_fix", "fix") );
add_shortcode( 'dinomitedays-make-html', array("dinomitedys_make_html_class", "make_html_files") );
add_shortcode( 'dinomitedays-process-upload', array("dinomitedys_upload", "process_upload") );
add_shortcode( 'dinomitedays-upload', array("dinomitedys_upload", "upload") );
add_shortcode( 'dinomitedays-last-seen', array("dinomitedays_misc_pages", "last_seen") );

/* -------------------------------------  cause it to happen

require_once "../dinomitedasys/plugin_update_check.php";
$MyUpdateChecker = new PluginUpdateChecker_2_0(
    'https://pluginserver.royweil.com/dinomitedays.php',
    __FILE__,
    'dinomitedays',
    1
);
*/
?>