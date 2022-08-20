<?php
/*		Freewheeling Easy Mapping Application
 *
 *		A collection of routines for display of trail maps and amenities
 *
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 */
class freewheeling_Segments {
    static public
    function freewheelingDisplaySegments( $atributes ) {
        global $eol, $errorBeg, $errorEnd;
        try {
            error_reporting( E_ALL | E_STRICT );
            $msg = "";
            $route = rrwUtil::fetchParameterString( "route", $atributes );
            if ( empty( $route ) )
                $route = "dobbs_freeport";
            $title = rrwUtil::fetchParameterString( "title", $atributes, "Trail Status - by Segments" );
            $start = rrwUtil::fetchParameterString( "start", $atributes, "" );
            $lastone = rrwUtil::fetchParameterString( "last", $atributes, "" );
            if ( empty( $lastone ) )
                $lastone = rrwUtil::fetchParameterString( "end", $atributes, "" );

            $displayKey = rrwUtil::fetchParameterBoolean( "displaykey", $atributes, true );
            //( $title );
            $msg .= freewheeling_Segments::printSegReportFromJson( $route, $title, $start, $lastone, $displayKey );
            return $msg;
        } catch ( Exception $e ) {
            $msg .= "E#404 " . $e->getMessage() . $eol;
            return $msg; 
        }
    }

    static public

    function printSegReportFromJson( $routeRequested, $title, $start, $lastone, $displayKey ) {
        global $eol, $errorBeg, $errorEnd;
        global $milepost;
        global $freewheelingeasy_images_URL;
        global $freewheelingeasy_stylesheet;

        $debugReport = rrwUtil::fetchParameterBoolean( "debugreport" );
        $debugJsonOpen = false;
        $debugLegs = false;

        $msg = "";
        if ( $debugReport )$msg .= "printSegReportFromJson( $routeRequested, $title, $start, $lastone, $displayKey ) ";
        $report = "";
        $report .= freewheeling_edit_setGlobals::freewheelingEditSetGobals( "printSegReportFromJson" );
        $report .= $freewheelingeasy_stylesheet;

        if ( $debugReport )$msg .= "into from processing printReport($routeRequested) $eol";
        $start = str_replace( "+", " ", $start );
        $start = str_replace( "%20", " ", $start );
        $start = str_replace( '"', "", $start );
        $lastone = str_replace( "+", " ", $lastone );
        $lastone = str_replace( "%20", " ", $lastone );

        $filename = "status-json/$routeRequested.json";
        if ( $debugLegs )$msg .= "Opening the legs file $filename $eol";
        $report .= freewheeling_edit_setGlobals::readFromServer( $filename, $legsText );
        $legs = json_decode( $legsText, true );

        if ( $debugLegs )$msg .= "Found " . count( $legs ) . " entries in the comment table $eol";
        if ( false )$msg .= rrwUtil::print_r( $legs, true, "the read legs fle" );

        $pastseg_commentid = -5;
        $color = rrwUtil::colorSwap();
        if ( empty( $start ) )
            $notYetAtStart = false;
        else
            $notYetAtStart = true;
        $trname = $legs[ 0 ][ "trname" ];
        unset( $legs[ 0 ] );
        if ( false )$msg .= rrwUtil::print_r( $legs, true, "the read legs fle" );

        if ( empty( $title ) ) {
            $title = "Trail status for $trname";
            if ( !empty( $start ) )
                $title .= " starting at $start ";
            if ( !empty( $start ) && !empty( $lastone ) )
                $title .= " and ";
            if ( !empty( $lastone ) )
                $title .= " ending at $lastone ";
        } else {
            //  use the existing title
        }
        $report .= "<h2>$title</h2>\n";
        $report .= "$eol Surface Key: <span class='colorMilesgreen'> xx miles on limestone or paved </span> &nbsp; 
						<span class='colorMilesYellow'> xx miles on shared road </span>&nbsp; 
						<span class='colorMilesBrown'> xx miles on unimproved ballas/dirt </span>&nbsp; 
						<span class='colorMilesRed'> xx miles on on closed trail</span> &nbsp; Surface Key$eol";
        $segCnt = 0;
        foreach ( $legs as $recseg ) {
            $segCnt++;
            //           if ( $segCnt == 1 )$msg .= rrwUtil::print_r( $recseg, true, "the read recseg fle" );
            $starticon = $recseg[ "starticon" ];
            if ( $notYetAtStart ) {
                if ( $debugReport )$msg .= "skipping compare strcmp( $starticon, $start ) $eol";
                if ( strcmp( $start, $starticon ) != 0 )
                    continue;
            }
            $notYetAtStart = false;

            if ( !empty( $lastone ) && strcmp( $lastone, $starticon ) == 0 ) {
                break;
            }
            $seg_commentid = $recseg[ "segmentid" ];
            $edithref = "<a href='https://edit.shaw-weil.com/edit-segment/" .
            "?segmentid=$seg_commentid&amp;route=$routeRequested&action=edit' target='editWindow'> ";
            $endicon = $recseg[ "endicon" ];
            $public = $recseg[ "descpublic" ];
            $publicSource = $recseg[ "publicSource" ];
            $internal = $recseg[ "descinternal" ];
            $title = $recseg[ "title" ];
            $starticonDisplay = freewheeling_Segments::CleanIconname( $starticon );
            $endiconDisplay = freewheeling_Segments::CleanIconname( $endicon );
            if ( array_key_exists( "startmiles", $recseg ) ) {
                $startmiles = $recseg[ "startmiles" ];
                $endmiles = $recseg[ "endmiles" ];
            } else {
                $startmiles = 0;
                $endmiles = 0;
                $msg .= "$errorBeg E#774 Milage does not exist for segment #$seg_commentid, $starticon to $endicon $errorEnd";
            }
            if ( is_null( $seg_commentid ) )
                continue;
            if ( !empty( $publicSource ) )
                $public .= "<span style='color:gray'> - Source: $publicSource</span>";
            $topLine = "$starticonDisplay (" . round( $startmiles, 1 ) . " mi) to $endiconDisplay (" . round( $endmiles, 1 ) . " mi)";
            $mileSegment = $endmiles - $startmiles;
            // build the Contents message
            $edithref = "<a href='https://edit.shaw-weil.com/edit-segment/";
            $edithref .= "?segmentid=$seg_commentid&amp;route=$routeRequested&action=edit' target='editWindow'> ";
            if ( !empty( $internal ) ) {
                $internal .= "$edithref Read More...</a>";
                $internal .= freewheeling_edit_setGlobals::editViewStart .
                "<br />\n --------------- VVVVVVVVVV -- confidential information -- VVVVVVVVVVVVVVVV --------------<br />$internal " .
                freewheeling_edit_setGlobals::editViewEnd;
            }
            $contents = "$public";

            // if ( current_user_can( "edit_posts" ) ) $contents .= $internal ;

            if ( strpos( $contents, "colorMile" ) === false ) {
                // miles class not specifed in the contents. calculate and preappend.
                if ( strpos( substr( $contents, 0, 10 ), "mile " ) === false ) {
                    if ( strpos( $public, "proposed " ) !== false ||
                        strpos( $public, "closed" ) !== false ) {
                        $MileClass = "class = 'colorMilesRed' ";
                    } elseif (
                        strpos( $public, "off road" ) !== false ||
                        strpos( $public, "off - road" ) !== false ||
                        strpos( $public, "off-road" ) !== false ) {
                        $MileClass = "class='colorMilesgreen'";
                    } elseif (
                        strpos( $public, "on road" ) !== false ||
                        strpos( $public, "existing road" ) !== false ||
                        strpos( $public, "on - road" ) !== false ||
                        strpos( $public, "on-road" ) !== false ||
                        strpos( $public, "Park Rd" ) !== false ||
                        strpos( $public, "wide street" ) !== false ||
                        strpos( $public, "wide ballast" ) !== false ||
                        strpos( $public, "on wide road" ) !== false ) {
                        $MileClass = "class='colorMilesYellow'";
                    } elseif (
                        strpos( $public, "ballast" ) !== false ||
                        strpos( $public, "existing dirt" ) !== false ||
                        strpos( $public, "packed dirt" ) !== false ) {
                        $MileClass = "class='colorMilesBrown'";
                    } else {
                        $MileClass = "class = 'colorMilesRed'";
                    }
                    $miles = "<span $MileClass>\n" . round( $mileSegment, 1 ) . " miles";
                    if ( $mileSegment > 5 ) {
                        for ( $ii = 5; $ii < $mileSegment; $ii++ )$miles .= "&nbsp; "; // lengthen the green line 
                    }
                    $contents = "$miles</span> on $contents";
                } else { // "mile" in first 10 characters 
                    $contents = "$contents";
                } // now display the result 
            } else {
                // contents contains colorMailes. do not insert milage	
            } // end if ( strpos( $contents, "class=colorMiles" )===false ) $color=rrwUtil::colorSwap( $color ); 

            $report .= "<div style='background-color:$color;' >
<h3>$edithref<img src='$freewheelingeasy_images_URL/penicon.gif' alt='Edit' /></a>" .
            "$topLine</h3><br />$contents </div>\n\n ";
        } // end   foreach ( $legs as $recseg )

        // at the bottom
        if ( true ) {
            $report .= "
<script>
doctitle = document.getElementsByClassName('entry-title');
console.log (doctitle);
console.log (doctitle).item(0).innerHTML ;
doctitle.innerHTML = '$title';
</script>
<br />
<hr>
<p><span class='colorMilesgreen'>mileage in green</span> is is designated
    Erie to Pittsburgh Trail that is on separated off road path $eol
    <span class='colorMilesYellow'>mileage in yellow </span> is designated Erie to Pittsburgh Trail
    that is on road $eol
    <span class='colorMilesBrown'>mileage in tan </span> is unfinished service (dirt or packed ballast)
    that is on road $eol 
    <span class='colorMilesRed'>mileage in red </span> is for section where there is no designated
    Erie to Pittsburgh Trail or trail is closed. $eol
    The length of the color is an indication of the length of the section. </p>$eol ";

            $site = get_site_url();
            if ( strpos( $site, "
                            edit.shaw-weil.com " ) !== false ) {
                $sqlmax = "
                            select max( segmentid ) + 1 from $rrw_segments ";
                $maxSeg = $wpdbExtra->get_var( $sqlmax );
                $report .= " <a href='https://edit.shaw-weil.com/edit-segment/?" .
                "segmentid=$maxSeg&amp;action=new&amp;route=$routeRequested'
                            target = 'edit' > Create a New Entry < /a> $eol";
            }
            //          $report .= "Thank You for reading this report";
        }
        if ( !empty( $msg ) )$msg .= $eol;
        return "$msg $report";
    }
    static private

    function CleanIconname( $iconname ) {
        global $eol, $errorBeg, $errorEnd;
        if ( substr( $iconname, 0, 3 ) == "LP " )
            $outIconname = substr( $iconname, 3 );
        else
            $outIconname = $iconname;
        return $outIconname;
    }

    static private

    function outputScriptsAndStyle( $title = "" ) {
        global $eol, $errorBeg, $errorEnd;
        global $freewheelingeasy_stylesheet;
        $msg = "";

        return $msg;
    }

} // end class

?>