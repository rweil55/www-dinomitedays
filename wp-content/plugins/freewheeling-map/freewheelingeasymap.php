<?php
//		Freewheeling Easy Mapping Application
//		A collection of routines for display of trail maps and amenities
//  	copyright Roy R Weil 2019 - https://royweil.com
/*
Plugin Name: FreeWheelingEasy Map
Plugin URI:  https://plugins.RoyWeil.com/freewheeling-easy-map/
Description: create shortcode for display of a Google Map with our (Roy's) trail lines
Author:      Roy Weil
Author URI:  https://RoyWeil.com
Donate URI: https://plugins.royweil.com/donate
Requires at least: 4.6.1
Tested up to: 5.4.2
depends: rrw-utilities-common
License: private
Version: 3.5.2.217 
Text Domain: freewheelingeasy-map
Domain Path: /lang
*/
global $trid, $trName, $trDoc, $trDate, $trVol, $trTime, $trNorth;
$trDoc = array();
require_once "display_stuff_class.php";
require_once "freewheelingeasy-wpdpExtra.php";
require_once "freewheelingeasy api.php";
require_once "freewheelingeasy-writeup.php";
require_once "freewheelingeasy-seg-reports.php";
require_once "freewheelingeasy-buildpages.php";
require_once "includes/polyline_inc.php";
function displaymap( $attr ) {
    global $eol, $errorBeg, $errorEnd;
    global $wpdbExtra, $rrw_icons, $rrw_business, $rrw_trails;
    global $savedAttributes;
    global $freewheelingeasy_images_URL;
    global $freewheeling_pagesPreBuiltUrl;
    global $freewheeling_pgherie;
    $msg = "";
    $debugQuotes = false;
    try {
        ini_set( "display_errors", true );
        error_reporting( E_ALL | E_STRICT );
        $debugDisplayMap = rrwUtil::fetchparameterString( "debugdisplaydap" );
        $debugDisplayMapGetInfo = rrwUtil::fetchparameterString( "debugdisplaymapgrtinfo" );
        if ( $debugDisplayMap ) {
            ini_set( 'upload_max_size', '256M' );
            ini_set( 'display_errors', true );
            ini_set( 'error_log', 'error.log' );
        }
        $html = "";
        $headerstylescript = "";
        //       $html.= rrwUtil::print_r($_POST, true, "Post it");
        $msg = freewheeling_edit_setGlobals::freewheelingEditSetGobals( "displaymap" );
        $headerstylescript .= "
		<style >
	.freewheeling-amenities-external {
	background-position: right center;
	background-repeat: no-repeat;
	background-image: linear-gradient(transparent, transparent), url('data:image/svg+xml,%3C%3Fxml%20version%3D%221.0%22%20encoding%3D%22UTF-8%22%3F%3E%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2210%22%20height%3D%2210%22%3E%3Cg%20transform%3D%22translate%28-826.429%20-698.791%29%22%3E%3Crect%20width%3D%225.982%22%20height%3D%225.982%22%20x%3D%22826.929%22%20y%3D%22702.309%22%20fill%3D%22%23fff%22%20stroke%3D%22%2306c%22%2F%3E%3Cg%3E%3Cpath%20d%3D%22M831.194%20698.791h5.234v5.391l-1.571%201.545-1.31-1.31-2.725%202.725-2.689-2.689%202.808-2.808-1.311-1.311z%22%20fill%3D%22%2306f%22%2F%3E%3Cpath%20d%3D%22M835.424%20699.795l.022%204.885-1.817-1.817-2.881%202.881-1.228-1.228%202.881-2.881-1.851-1.851z%22%20fill%3D%22%23fff%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E');
	padding-right: 13px;
}
</style>
";
        $scriptDire = plugins_url( "" ) . "/freewheeling-map";
        $msg = freewheeling_edit_setGlobals::freewheelingEditSetGobals( "xaddScriptsToHeader" );
        $apiKey = freewheeling_edit_setGlobals::getAPIkey();
        $headerstylescript .= "
<!-- scripts added by the freewheeling Easy plugin  ========================== -->
<script type='text/javascript' 
    src='https://maps.googleapis.com/maps/api/js?$apiKey" .
        "&libraries=geometry&libraries=drawing'></script>
<script type='text/javascript' src='$scriptDire/freewheelingeasy-map.js' > </script>
<script type='text/javascript' src='$scriptDire/freewheelingeasy-points.js' > </script>
<script type='text/javascript' src='$scriptDire/freewheelingeasy-lines.js' > </script>
<script type='text/javascript' src='$scriptDire/markerclustererplus-4.0.1.min.js'></script>
<!-- end of scripts added by the freewheeling Easy plugin-->
	
	";
        $savedAttributes = $attr;
        //		$headerstylescript .= rrwUtil::print_r($_POST, true, "Post");
        //		$headerstylescript .= rrwUtil::print_r($savedAttributes, true, "attributes");
        $bizid = rrwUtil::fetchparameterAny( "bizid", $attr );
        $bizNameRequest = rrwUtil::fetchparameterAny( "bizname", $attr );
        $counties = rrwUtil::fetchparameterString( "counties", $attr );
        $dino = rrwUtil::fetchparameterBoolean( "dino", $attr );
        $gapWeight = rrwUtil::fetchparameterString( "gapweight", $attr );
        $iconname = rrwUtil::fetchparameterString( "iconname", $attr );
        $iconid = rrwUtil::fetchparameterString( "iconid", $attr );
        $kmlmap = rrwUtil::fetchparameterString( "kmlmap", $attr );
        $linename = rrwUtil::fetchparameterString( "linename", $attr );
        $latitude = rrwUtil::fetchparameterString( "latitude", $attr );
        $longitude = rrwUtil::fetchparameterString( "longitude", $attr );
        $route = rrwUtil::fetchparameterString( "route", $attr );
        $showInstructions = rrwUtil::fetchparameterString( "showinstructions", $attr );
        $trailid = rrwUtil::fetchparameterString( "trailid", $attr );
        $trailmileposts = rrwUtil::fetchparameterString( "trailmileposts", $attr );
        if ( $debugQuotes ) print "$bizNameRequest fetched -- $bizNameRequest $eol";
        $bizNameRequest = str_replace( "--rrwA--", "&", $bizNameRequest );
        $bizNameRequest = str_replace( "--rrwQ--", "'", $bizNameRequest );
        $bizNameRequest = stripslashes( $bizNameRequest );
        if ( empty( $bizNameRequest ) && !empty( $bizid ) ) {
            $sqlBizname = "select bizname from $rrw_business where bizid = '$bizid'";
            $bizNameRequest = $wpdbExtra->get_var( $sqlBizname );
            if ( $wpdbExtra->num_rows != 1 )
                throw new Exception( "$msg $errorBeg E#924 got id but not name
                                    $errorEnd $sqlBizname $eol" );
        }
        if ( empty( $gapWeight ) )
            $gapWeight = 3;
        $highlightGaps = rrwUtil::fetchparameterString( "highlightgaps", $attr );
        $trail2biz = rrwUtil::fetchparameterString( "trail2biz", $attr );
        // ---------------------------------------------------------- determinr trail for counds
        $inputtrail = rrwUtil::fetchparameterString( "trail_route_name", $attr );
        if ( empty( $inputtrail ) )
            $inputtrail = rrwUtil::fetchparameterString( "trailid", $attr );
        if ( empty( $inputtrail ) )
            $inputtrail = rrwUtil::fetchparameterString( "trail", $attr );
        if ( empty( $inputtrail ) )
            $inputtrail = rrwUtil::fetchparameterString( "trailname", $attr );
        $iiParen = strpos( $inputtrail, "(" );
        if ( $iiParen !== false ) {
            // extract the trail name, discard the route info
            $inputtrail = substr( $inputtrail, 0, $iiParen - 1 );
        }
        if ( $dino ) { // find the display range
            if ( empty( $latitude ) ) {
                $north = 40.45993;
                $south = 40.42123;
                $east = -79.93846;
                $west = -80.02305;
            } else {
                $inc = .003;
                $north = $latitude + $inc + .0005;
                $south = $latitude - $inc;
                $east = $longitude + $inc;
                $west = $longitude - $inc;
            }
            $trailid = "Dinosaurs of Pittsburgh";
            $trailname = "Dinosaurs of Pittsburgh";
            $inputtrail = "Dinosaurs of Pittsburgh";
            $routesRequestedSql = "";
        } elseif ( empty( $inputtrail ) && ( !empty( $iconname ) || !empty( $iconid ) ) ) {
            $sqlicon = "select latitude, longitude, trailid, trName
                    from $rrw_icons  
					join $rrw_trails on trid = trailid 
                    where iconname = '$iconname' or iconid ='$iconid' ";
            $recs = $wpdbExtra->get_resultsA( $sqlicon );
            if ( 1 != $wpdbExtra->num_rows ) {
                throw new Exception( "$errorBeg E#426 icon $iconname, #$iconid
                        not found $errorEnd $sqlicon $eol" );
            }
            $rec = $recs[ 0 ];
            $inc = .0003;
            $north = $rec[ "latitude" ] + $inc;
            $south = $rec[ "latitude" ] - $inc;
            $east = $rec[ "longitude" ] + $inc;
            $west = $rec[ "longitude" ] - $inc;
            $trailid = $rec[ "trailid" ];
            $trailname = $rec[ "trName" ];
            $inputtrail = $trailname;
            $routesRequestedSql = $trailid;
        } else {
            $thisTrail = freewheeling_edit_setGlobals::getTrailInfo( $inputtrail );
            if ( $debugDisplayMap ) print rrwUtil::print_r( $thisTrail, true,
                "results getinfo for $inputtrail" );
            $north = $thisTrail[ "north" ];
            $south = $thisTrail[ "south" ];
            $east = $thisTrail[ "east" ];
            $west = $thisTrail[ "west" ];
            $trailid = $thisTrail[ "trailid" ];
            $trailname = $thisTrail[ "trname" ];
            $inputtrail = $trailname;
            $routesRequestedSql = $thisTrail[ "sqlWhere" ];
        }
        $routesRequestedList = str_replace( "%", "", $routesRequestedSql );
        $routesRequestedList = str_replace( "routes like", "", $routesRequestedList );
        $routesRequestedList = str_replace( " or ", ", ", $routesRequestedList );
        $routesRequestedList = str_replace( "trailid =", "", $routesRequestedList );
        $routesRequestedList = str_replace( "%", "", $routesRequestedList );
        if ( false )$headerstylescript .= "routesRequestedSql = $routesRequestedSql, routesRequestedList = $routesRequestedList $eol";
        if ( !empty( $bizNameRequest ) ) {
            global $wpdbExtra, $rrw_business;
            // got a businedss nam - set north on that
            $sqlBiz = "select bizlat, bizlng from $rrw_business
            where bizName = %s";
            $sqlBizPrepare = $wpdbExtra->prepare1arg( $sqlBiz, $bizNameRequest );
            if ( $debugQuotes ) print "$bizNameRequest -- $sqlBizPrepare $eol";
            $recbizs = $wpdbExtra->get_resultsA( $sqlBizPrepare );
            if ( $wpdbExtra->num_rows == 0 ) {
                $html = "$errorBeg E#870 $bizNameRequest not found $eol $sqlBizPrepare $errorEnd";
            } else {
                if ( $recbizs[ 0 ][ "bizlat" ] == 0 || $recbizs[ 0 ][ "bizlng" ] == 0 )
                    throw new Exception( "$errorBeg E#918 business missing 
                                            latitude, longitude$errorEnd" );
                $latIncrument = .06;
                $north = $recbizs[ 0 ][ "bizlat" ] + $latIncrument;
                $south = $recbizs[ 0 ][ "bizlat" ] - $latIncrument;
                $east = $recbizs[ 0 ][ "bizlng" ] + $latIncrument;
                $west = $recbizs[ 0 ][ "bizlng" ] - $latIncrument;
            }
        }
        $headerstylescript .= "\n\n
        <!-- ====================================================================  -->
<script>
        var freewheeling_logStyles = 'Color:#ff0000;';
        var freewheeling_debug_fileload = false;
        var freewheeling_debugLineTotals = false;
        var freewheeling_debugIconTotals = false; 
        var freewheeling_getDataUrl = '" .
        plugins_url( "/freewheelingeasy-getfile.php/?file=/data/", __FILE__ ) . "'
        var freewheeling_pagesPreBuiltUrl = '$freewheeling_pagesPreBuiltUrl';
        var freewheelingeasy_images_URL = '$freewheelingeasy_images_URL';
        var freewheeling_pgherie = '$freewheeling_pgherie';
        
        var freeWheeling_kmlmap = \"$kmlmap\";
        var freeWheeling_route = \"$route\";
        var freewheeling_bizNameRequest = \"$bizNameRequest\";
        ";
        // check boxes that set variables
        // $headerstylescript .= fetchandVar( "endpoints", "endpoints", "none", "endpoints" );
        // milepost of various flavors
        $mileFiles = ""; //"point file name |point|zoom in| zoom out ,";
        $linesSource = ""; // "linefilename|line"
        $seondSource = ""; // display immediately after the llines
        // always display miles along the trails
        $mileFiles .= "milepost_10|mile10,";
        $mileFiles .= "milepost_05|mile05,";
        $mileFiles .= "milepost_01|mile01,";
        if ( rrwUtil::fetchparameterBoolean( "traildivide", $attr ) ) {
            $mileFiles .= "traildevide|amen,";
        }
        if ( rrwUtil::fetchparameterBoolean( "traillabels", $attr ) ) {
            $seondSource .= "traillable|traillable,"; // first thing is the trail names
        }
        if ( rrwUtil::fetchparameterBoolean( "amenities", $attr ) ) {
            $mileFiles .= "icons_amen|amen,";
            $showAmities = true; // display the selection header
        } else {
            $showAmities = false;
        }
        // various milepost information 
        if ( rrwUtil::fetchparameterBoolean( "allmileposts", $attr ) ) {
            $mileFiles .= "launch|launch,milepost_other|launch,";
        }
        if ( rrwUtil::fetchparameterBoolean( "businesslocations", $attr ) ) {
            $mileFiles .= "businesslocations|location,";
        }
        if ( rrwUtil::fetchparameterBoolean( "hideamenities", $attr ) ) {
            $showAmities = false;
        } else {
            $mileFiles .= "icons_amen|amen,";
            $showAmities = true; // display the selection header
        }
        if ( rrwUtil::fetchparameterBoolean( "hidecamp", $attr ) ) {} else {
            $mileFiles .= "icons_camp|camp,";
        }
        if ( rrwUtil::fetchparameterBoolean( "hideparking", $attr ) ) {} else {
            $mileFiles .= "icons_park|park,";
        }
        $mileFiles = substr( $mileFiles, 0, strlen( $mileFiles ) - 1 ); // remove trailing comma
        //  --------------------------------------------------------- which specific item
        //        print "mileFiles $mileFiles $eol";
        $msg .= "testing $route -- $trailid $eol";
        if ( !empty( $linename ) && !empty( $bizNameRequest ) ) {
            $msg .= "$errorBeg E#401 not allowed ask for both a pecific line and specific business$errorEnd";
            $headerstylescript .= "    var freewheeling_desc_search = '';\n";
        } elseif ( !empty( $linename ) ) {
            $headerstylescript .= "    var freewheeling_desc_search = '$linename';\n";
        } elseif ( !empty( $bizNameRequest ) ) {
            $headerstylescript .= "    var freewheeling_desc_search = '$bizNameRequest';\n";
        } elseif ( !empty( $route ) && !empty( $trailid ) ) {
            $headerstylescript .= "    var freewheeling_desc_search = '$trailname';\n";
        } else {
            $headerstylescript .= "    var freewheeling_desc_search = '';\n";
        }
        //  --------------------------------------------------------- which lines
        $linesSource .= "zz_lines|line,";
        if ( $trail2biz == "on" )
            $linesSource .= "zz_bizroute|line,";
        if ( !empty( $bizNameRequest ) ) {
            $routesRequestedList = "\"$bizNameRequest\"";
            $linesSource = "zz_lines|line,zz_bizroute|line,"; // just one business
        }
        if ( $counties == "on" ) {
            $linesSource .= "zz_county|line,";
        }
        //  -------------------------------------------------------- which amenities
        if ( $trailmileposts == "on" ) {
            $routesRequestedList = "'mileposts', $routesRequestedList";
        }
        if ( $highlightGaps == "on" ) {
            $gasweight = 10;
        }
        if ( $dino )
            $headerstylescript .= "
                var FreeWheelingEsay_linesSource  = 'dinomites|dino';
                ";
        else
            $headerstylescript .= "
                var FreeWheelingEsay_linesSource  = '$seondSource$linesSource$mileFiles';
                ";
        $headerstylescript .= "
            var	FreewheelingEasy_GapWeight= $gapWeight;
</script>
";
        // ====================================================================== 
        $titleline = $trailname;
        if ( !empty( $linename ) )
            $titleline .= " - $linename";
        if ( !empty( $iconname ) )
            $titleline .= " - $iconname";
        if ( !empty( $bizNameRequest ) )
            $titleline .= " - $bizNameRequest";
        //       $titleline = str_replace("'","\'", $titleline);
        $html .= "
<table width='100%'><tr>
<td>&nbsp;</td>
<td style='font-size:24pt; font-weight:bold'> 
	$titleline</td><td style='font-size:8pt; test-align:right'>
	This map brought to you by the book 
	<a href='https://freewheelingeasy.com' class='freewheeling-amenities-external' > 
	Freewheeling Easy in Western Pennsylvania</a> .
</td></tr>
</table> 
";
        //        if ( $showAmities )$html .= "
        //<div style='font-size:8pt;' >
        //	Display - <input type='checkbox' checked id='Parking' 
        //	onclick='turnit(this, \"park\")'>parking &nbsp; &nbsp; 
        //	<input type='checkbox' checked id='indoor' 
        //	onclick='turnit(this, \"indoor\")'>Indoor lodging &nbsp; &nbsp; 
        //	<input type='checkbox' checked  id='camp' 
        //	onclick='turnit(this,\"camp\")'>Campground &nbsp; &nbsp; 
        //	<input type='checkbox' checked id='restaurant'
        //	onclick='turnit(this, \"restaurant\")' >Restaurant &nbsp; &nbsp; 
        //	<input type='checkbox' checked id='bar'
        //	onclick='turnit(this, \"bar\")' >Bar &nbsp; &nbsp; 
        //	<input type='checkbox' checked id='grocery'
        //	onclick='turnit(this, \"grocery\")' >Grocery &nbsp; &nbsp; 
        //	<input type='checkbox' checked  id='bikeshop'
        //	onclick='turnit(this, \"bike\")'>bike shop &nbsp; &nbsp; 
        //</div>";
        if ( !empty( $showInstructions ) ) {
            $html .= "<strong>instructions:</strong> Use this map to verify trail alignment and condition. 
					<ul><li>Scroll over a trail segment to see its name. </li>
					<li><a href='#linework'>below the map </a> is a table of lines with distance</li>
					<li>Clickng a line name will open a map in a new window with just that line displayed</li>
					<li>Spelling of names is sloppy click the pencil icon to edit</li>
					</ul>
		";
        }
        $html .= "	
        <div id='zoomlevel'>zoomlevel goes here  </div><br />
		<div id='mapCanvas' style='width:100px; height:100px; margin:0; padding:5px' >	
		one moment while we calculate the line work	 
		</div>
        ";
        if ( $dino ) {
            $html .= "<div id='legend' heught='50px'> <img src='$freewheelingeasy_images_URL/legenddino.svg' 
                                       /></div>
            ";
        } else {
            $html .= "<div id='legend' > <img src='$freewheelingeasy_images_URL/legend.svg' 
                                        width='200px' /></div>
            ";
        }
        $html .= "
<!-- invoke resizeMapCanvas2Page and google.maps.event.addDomListener(window, 'load' -->
<script  type='text/javascript'>
    //<![CDATA[[
        _freewheelingmap_ResizeMapCanvas2Page ('mapCanvas', 100, 80, $north, $south, $east, $west); 
		window.addEventListener('load', _freewheelingmap_Initialize('mapCanvas', true, \"$titleline\") );
        var mapcanvasObject = document.getElementById('mapCanvas');
        mapcanvasObject,moveTo(0,0);
    //]]>
</script>
<noscript><p>Google maps requires that JavaScript be enabled.</p></noscript>
<!-- ========================================================================== --> 
";
        if ( !empty( $trailname && !empty( $showInstructions ) ) ) {
            $file = "/miles_by_type/$trailid-routedetail.html";
            $html .= "<span id='linework'>$eol</span>" . freewheelingWriteUpFile( $file );
        }
        if ( current_user_can( 'administrator' ) || true ) {
            $html .= "<div>";
        } else
            $html .= "<div style='display:none' > ";
        $html .= "<br /><button id='allowdrag' value='no' onclick='this.value=\"allow\";
        this.innerHTML=\"be careful not to drag\";' >
             Allow permanent drag placement.</button></div> ";
        $msg = $headerstylescript;
        $msg .= freewheel_encapsulateWithCopyright( $html );
        return $msg; //  this goes to the screen 
    } catch ( Exception $ex ) {
        return "$html " . $ex->getMessage();
    } // end catch
} // end funvion 
class Google_cost_increase {
    static public function googleCostIncreaseURL( $url ) {
        global $wpdbExtra, $rrw_codes, $rrw_googlebilling;
        global $eol, $errorBeg, $errorEnd;
        /*  
         *     updates the google costing table usng the url to determine function and api
         */
        $msg = "";
        $debugResult = false;
        //      $msg .= rrwFormat::backtrace( " I#769  googleCostIncreaseURL"
        $kk = strpos( $url, "key=" ); // exract the apikey from the URL
        if ( false === $kk )
            throw new Exception( "$msg $errorBeg E#852 no key in the URL $errorEnd $url $eol " );
        $apiKey = substr( $url, $kk + 4 );
        if ( false !== strpos( $apiKey, "&" ) )
            throw new Exception( "$msg $errorBeg E#853 key not at end url $errorEnd $url $eol " );
        // extract the type of request from the URL
        if ( strpos( $url, "nearbysearch" ) )
            $item = "nearby";
        elseif ( strpos( $url, "textsearch" ) )
        $item = "textsearch";
        elseif ( strpos( $url, "phone" ) )
        $item = "contact";
        elseif ( strpos( $url, "autocomplete" ) )
        $item = "details";
        elseif ( strpos( $url, "details" ) )
        $item = "details";
        elseif ( strpos( $url, "rating" ) )
        $item = "clould";
        elseif ( strpos( $url, "direction" ) )
        $item = "direction";
        else
            $msg .= "$errorBeg E#957 unknow cost type $errorEnd $url $eol";
        // ---------------------------------------------- now counting
        if ( empty( $rrw_googlebilling ) )
            throw new exception( "$msg $errorBeg E#703 empty table name $errorEnd" );
        $where = "where  billyearmonth = '" . date( "Y-m" ) . "' and  billapikey = '$apiKey' and
                        billdescription = '$item' ";
        $cntRow = $wpdbExtra->query( "update $rrw_googlebilling set billcount = billcount + 1 $where " );
        if ( ( false !== $cntRow ) && ( 1 == $cntRow ) ) {
            if ( $debugResult )$msg .= "updated $cntRow rows for $apiKey - $item  $eol";
        } else {
            // probaly did not exist
            $newData = array(
                "billyearmonth" => date( "Y-m" ),
                "billapikey" => $apiKey,
                "billdescription" => $item,
                "billcount" => 1,
            );
            $cnt2 = $wpdbExtra->insert( $rrw_googlebilling, $newData );
            if ( 1 != $cnt2 ) {
                $msg .= " $errorBeg E#802 insert of new google billing failed $errorEnd";
                $msg .= rrwUtil::print_r( $newData, true, "Data to insert" );
                throw new Exception( $msg );
            } else {
                if ( $debugResult )$msg .= "new billing entry created for $apiKey - $item $eol";
            }
        } // end  if ( false !== cntRow )
        return $msg;
    }
}
add_shortcode( 'freewheeling-easy-map', 'displaymap' );
add_shortcode( 'freewheelingeasymap', 'displaymapbizna,e' );
// add_shortcode( 'freewheeling-easy-map-trailhead', 'displayTrailHeads' );
add_shortcode( 'freewheeling-easy-map-OLC-explain', 'displayOLCexplinations' );
add_shortcode( 'freewheeling-easy-map-write-up', 'freewheelingWriteUp' );
add_shortcode( 'freewheeling-easy-map-segments', array( "freewheeling_Segments", "freewheelingDisplaySegments" ) );
add_shortcode( 'freewheeling-easy-map-segments-e2p', array( "freewheeling_Segments", "freewheelingSegmentE2Preport" ) );
add_shortcode( 'freewheeling-easy-map-buildpages', array( "freewheelingEasy_buildpages", "buildpages" ) );
// -------------------------------------  cause it to happen
register_activation_hook( __FILE__, array( 'freewheelingEasy_buildpages', 'buildpages' ) );
require_once "plugin_update_check.php";
$MyUpdateChecker = new PluginUpdateChecker_2_0(
    'https://pluginserver.royweil.com/freewheeling-map.php',
    __FILE__,
    'freewheeling-map',
    1
);
function fetchandVar( $fetch, $varName, $default, $ifthere ) {
    global $savedAttributes;
    $file = rrwUtil::fetchparameterString( $fetch, $savedAttributes );
    $file = rrwUtil::fetchparameterString( $fetch, $savedAttributes );
    $file = rrwUtil::fetchparameterString( $fetch, $savedAttributes );
    if ( empty( $file ) ) {
        $temp = $default;
    } elseif ( $file == "on" ) {
        $temp = true;
    } else {
        if ( strpos( $ifthere, "%" ) !== false )
            $temp = str_replace( "%", $file, $ifthere );
        else
            $temp = $ifthere;
    }
    if ( $temp == 'true' || $temp == 'false' )
        $temp = "	var $varName = $temp; \n";
    else
        $temp = "	var $varName = '$temp'; \n";
    return $temp;
}
?>