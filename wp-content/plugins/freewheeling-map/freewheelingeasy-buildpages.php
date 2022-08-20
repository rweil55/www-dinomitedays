<?php
/*		Freewheeling Easy Mapping Application
 *
 *		A collection of routines for display of trail maps and amenities
 *
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 */
/*
	Creates/updates the pages that are required by the mapping plugin
	
	Freewheelingeasy-google-map		[freewheeling-easy-map ]
	freewheelingeasy-write-up		[freewheeling-easy-map-write-up ]
[\r\n]{2,}
	freewheelingeasy-amenities		[freewheeling-easy-map-write-up ]
*/
Class freewheelingEasy_buildpages {
	public static
	function buildpages() {
		global $eol, $errorBeg, $errorEnd;
		try {
			$debugBuildPage = false;
			$msg = "";
			$msg .= freewheelingEasy_buildpages::buildPage( "Freewheelingeasy-google-map", "Google Map",
														   "[freewheeling-easy-map ]" );
	//		$msg .= freewheelingEasy_buildpages::buildPage( "freewheelingeasy-google-mawrite-upp", "Temp Title",
	//													   "[freewheeling-easy-map-write-up ]" );
			$msg .= freewheelingEasy_buildpages::buildPage( "freewheelingeasy-amenity", "Display an Amenity",
														   "[freewheeling-easy-map-write-up ]" );
		} catch ( Exception $ex ) {
			$msg .= "$erroeBeg E#632 error " . $ex->getMessage() . " during build pages $errorEnd";
		}
		if ($debugBuildPage) return $msg;
	}
	private static
	function buildPage( $pageSlug, $pageTitle, $content ) {
		global $eol, $errorBeg, $errorEnd;
		global $wpdb;
		// do not do this again. it unlnks comments
		$msg = "buildPage( $pageSlug, $pageTitle, $content ) ... ";
		//  ---------------------------------------- Delete the past pages
		$postTable = $wpdb->prefix . "posts";
		$num = $wpdb->get_var( "SELECT ID FROM $postTable WHERE post_name = '" . $pageSlug . "'" );
		$num = $wpdb->get_var( "SELECT ID FROM $postTable WHERE post_name = '" . $pageSlug . "'" );
		if ( !is_null( $num ) ) {
			$msg .= " Page id #$num already exists. $eol ";
		} else {
			//  --------------------------------------- build the pages
			$user_id = get_current_user_id();
			$defaults = array(
				'ID' => 0,
				'post_author' => $user_id,
				'post_content' => "$content",
				'post_title' => "$pageTitle",
				'post_name' => "$pageSlug",
				'post_status' => 'publish',
				'post_type' => 'page',
				'comment_status' => 'closed'
			);
			
//				$msg .= rrwUtil::print_R($defaults, true, "New page data");
			$newPageNum = wp_insert_post( $defaults );
			$msg .= " Page #$newPageNum has been nstalled. $eol";
		}
		return $msg;
	}
}
?>