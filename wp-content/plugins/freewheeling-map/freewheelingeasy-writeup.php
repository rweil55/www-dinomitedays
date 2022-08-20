<?php
/*		Freewheeling Easy Mapping Application
 *		A collection of routines for display of trail maps and amenities
 *		copyright Roy R Weil 2019 - https://royweil.com
 
 *	freewheelingWriteUp( $attributeArray )  
 *		reads and displays a prebuitlt file at $freewheeling_pagesPreBuiltUrl/...
 *			$attributeArray.amenity	- name of an amenoty file in prebuilt-pages/amenity
 *			$attributeArray.file -	- as ubdirectory/file name of a file on the prebuilt-pages directory
 *
 *  freewheelingWriteUpFile( $file )
 *		reads and displays a prebuitlt file at $freewheeling_pagesPreBuiltUrl/...
 *			amenity	- name of an amenoty file in prebuilt-pages/amenity
 *			file -	- as ubdirectory/file name of a file on the prebuilt-pages directory
 *
 *  function freewheel_encapsulateWithCopyright( $html ) 
 *		Adds a copyright notice on the left and right of the HTML contents
 *			html - content to be encapsulated
 *
 *  freewheeling_edit_setGlobals.freewheeling_map_setconstants
 *		Sets  several global constatns, used throughout the code
 *      global $meters2Miles;
 *      global $freewheelingeasy_images_URL; 
 *      global $rrw_pagesPreBuiltDire, $freewheeling_pagesPreBuiltUrl;
 *      global $freewheelingeasy_kml_directory;
 *      global $eol, $errorBeg, $errorBeg, $errorEnd;
 *
 *  freewheeling_edit_setGlobals.freewheelingEditSetGobals("locatn called from") {
 *		Sets global thaat are used to access the database tables
 *			global $rrw_codes, $rrw_codes_amenities, $rrw_codes_surface;
 *			global $rrw_bizaccsvc, $rrw_businessservices, $rrw_trailicons, $rrw_traillines;
 *			global $rrw_icons, $rrw_lines, $rrw_regions, $rrw_foure_fixes, $rrw_trails;
 *			global $wpdbExtra, $rrw_history, $rrw_business, $rrw_services;
 *			global $rrw_trail_routes, $rrw_segments, $rrw_trail_mile, $rrw_trail_rating_set;
 *          global $rrw_mileposts
 *
 */
require_once "display_stuff_class.php";
require_once "includes/freewheelingeasy-map-formats.php";
class freewheeling_WriteUp {
    public static function formatDataTxtFileName( $middle ) {
        global $eol, $errorBeg, $errorEnd;
        $debugdata = false;
        if ( strpos( $middle, "/" ) === false )
            $middle = "data/$middle";
        if ( strpos( $middle, "." ) === false )
            $middle = "$middle.txt";
        $middle = rrwUtil::sanitize_file_name( $middle );
        // sanitize_file_name removes stuff, changes space to minus
        if ( $debugdata ) print "$middle $eol ";
        return $middle;
    }
} // end class
function freewheelingWriteUp( $attributeArray ) {
    global $eol, $errorBeg, $errorEnd;
    global $rrw_pagesPreBuiltDire;
    $eol = "<br />\n";
    $msg = "";
    try {
        $debugParameters = false;
        $msg .= freewheeling_edit_setGlobals::freewheelingEditSetGobals( "freewheelingWriteUp" );
        if ( $debugParameters )$msg .= rrwUtil::print_r( $attributeArray, true, "callingparamaters" );
        $amenity = rrwUtil::fetchParameterString( "amenity", $attributeArray );
        $bookname = rrwUtil::fetchParameterString( "bookname", $attributeArray );
        $trailid = rrwUtil::fetchParameterString( "trailid", $attributeArray );
        $file = rrwUtil::fetchParameterString( "file", $attributeArray );
        if ( empty( $bookname ) )
            $bookname = $trailid; // problay will not work because of case
        $back_color = get_option( "freewheelingeasy_menu_footer_background_color" );
        if ( empty( $back_color ) )
            $back_color = "Light Purple Blue"; // dark blue
        if ( !empty( $bookname ) ) {
            $file = "/trails-html/$bookname.html";
            $msg .= rrwFormat::sellTheBook();
            $fileNameFull = "$rrw_pagesPreBuiltDire$file";
            if ( !file_exists( $fileNameFull ) ) {
                $trailinfo = freewheeling_edit_setGlobals::getTrailInfo( $bookname );
                $file = "/trails-html/" . $trailinfo[ "trHtmlfileName" ] . ".html";
            }
            $msg .= freewheelingWriteUpFile( $file, false );
            $msg .= "   <script>
                        trailName = document.getElementsByClassName('entry-title');
                        trailName.Style.fontWeight = 'Bolder';
                        trailName.Style.fontSize = '14px';
                        </script>
                        ";
            return $msg;
        } elseif ( !empty( $amenity ) ) {
            $file = "amenities/" . str_replace( "/", "_", $amenity ) . ".php";
        } elseif ( empty( $file ) ) {
            $msg .= rrwUtil::$errorBeg . " E#480 No paraameter given. <a href='/' >
							Now what</a>" . rrwUtil::$errorEnd;
            $msg .= rrwUtil::print_r( $attributeArray, true, "parameters list from the shortcode on the page" );
            $msg .= rrwUtil::print_r( $_GET, true, "parameters list from the url" );
            return $msg;
        }
        $msg .= freewheelingWriteUpFile( $file );
        if ( !empty( $bookname ) ) {
            $msg .= "   <script>
    trailName = document.getElementsByClassName('entry-title');
    trailName.Style.fontWeight = 'Bolder';
    trailName.Style.fontSize = '14px';
    </script>
    ";
        }
        return $msg;
    } catch ( Exception $e ) {
        $msg .= $e->getMessage() . $eol;
        return $msg;
    } // end catch
}
function freewheelingWriteUpFile( $file, $santizize = true ) {
    // $ file is now set to somenthing.
    global $eol, $errorBeg, $errorEnd;
    global $rrw_pagesPreBuiltDire;
    global $freewheeling_pagesPreBuiltUrl;
    $msg = "";
    $debugResource = false;
    try {
        $msg .= "\n<!-- freewheelingWriteUpFile($file) -->\n";
        $fileNameCleaned = freewheeling_WriteUp::formatDataTxtFileName( $file );
        $fileNameFull = "$rrw_pagesPreBuiltDire/$fileNameCleaned";
        $fileNameFull = str_replace( "//", "/", $fileNameFull );
        if ( !file_exists( $fileNameFull ) ) {
            $msg .= "<!-- looking for file '$fileNameFull', but it does not exists -->";
            if ( strpos( $file, "amenities" ) !== false ) {
                $iiSlash = strrpos( $file, "/" );
                $iconname = substr( $file, $iiSlash + 1 );
                $iconname = str_replace( ".php", "", $iconname );
                $msg .= "<a href='https://edit.shaw-weil.com/makehtml/?iconname=$iconname' >
                            make amenity</a> $eol";
            }
            throw new Exception( "$msg $errorBeg E#589 invalid parameter of '$file' to the display routine $errorEnd" );
        }
        $msg .= "<!-- look for $file found the file $fileNameFull process it  --> \n";
        $fp = fopen( $fileNameFull, "r" );
        $html = fread( $fp, filesize( $fileNameFull ) );
        fclose( $fp );
        $html = str_replace( "</body>", "", $html );
        $html = str_replace( "</html>", "", $html ); {
            if ( $debugResource )$msg .= "start 1 of file $fileNameCleaned: $eol" .
            htmlspecialchars( substr( $html, 0, 30 ) ) . $eol;
            // this code deals with orginal format of the word file
            if ( $iiwipeout = strpos( $html, "<!-- end of the bufferBeg -->" ) ) {
                $html = substr( $html, $iiwipeout );
            }
            $html = str_replace( "fweTrailer();", "", $html );
            //		$html = str_replace( "$resourceName", $newResource, $html );
            if ( $debugResource )$msg .= "start 2 of file $fileNameCleaned: $eol" .
            htmlspecialchars( substr( $html, 0, 30 ) ) . $eol;
        }
        // this code deals with the indesign version of the html file
        {
            // in the html file there is 
            //  src=Allegheny_River_Trail - web - resources / image / belmar - ramp - 569. jpg
            // this converts that into a URL 
            //	https://freewheelingeasy.com/https://freewheelingeasy.com//prebuilt-pages/trails-html/
            //	Allegheny_River_Trail-web-resources/image/belmar-ramp-569.jpg
            // Note: file=trails-html/Allegheny%20River%20Trail.html
            $iiDot = strrpos( $file, "." );
            $basedire = substr( $file, 0, $iiDot ); // trails-html/Allegheny%20River%20Trail
            $basedire = str_replace( " ", "_", $basedire );
            $basedire = str_replace( "%20", "_", $basedire ); // trails-html/Allegheny_River_Trail
            $iislash = strrpos( $basedire, "/" );
            $prebuiltsubdire = substr( $basedire, 0, $iislash );
            if ( $debugResource )$msg .= "prebuilt subdire of '$prebuiltsubdire' $eol ";
            $basedire = substr( $basedire, $iislash + 1 ); // Allegheny_River_Trail
            $searchDire = "$basedire-web-resources";
            if ( $debugResource )$msg .= "looking for searchDire of '$searchDire' $eol ";
            $baseURL = "$freewheeling_pagesPreBuiltUrl$prebuiltsubdire";
            if ( $debugResource )$msg .= "looking recplae with '$baseURL' $eol ";
            $html = str_replace( $searchDire, "$baseURL/$searchDire", $html );
            $baseDireSpaces = str_replace( "_", "", $basedire );
            //       print "$basedire goes to $baseDireSpaces ";
            $html = str_replace( "href = \"$basedire", "href=\"$baseURL/$baseDireSpaces", $html );
            if ( $debugResource )$msg .= "start of file:step2 $eol" .
            htmlspecialchars( substr( $html, 0, 30 ) ) . $eol;
        }
        // this code deals with cross references that are not there
        //if looks for href="" and removes the anchor (A) atags.
        list( $msgTemp, $html ) = freewheeling_removeTagItem( $html, 'href=""' );
        $msg .= $msgTemp;
        list( $msgTemp, $html ) = freewheeling_removeTagItem( $html, 'DOCTYPE' );
        if ( $debugResource )$msg .= "start of file:step 3 $eol" .
        htmlspecialchars( substr( $html, 0, 30 ) ) . $eol;
        // this code deals with login vs not login display
        { // it removes text between editViewStart and editViewEnd
            if ( $debugResource )$msg .= "permissin says: " .
            current_user_can( "edit_posts" ) . $eol;
            if ( !current_user_can( "edit_posts" ) ) {
                $iiStart = 1;
                $cnt = 0;
                if ( $debugResource )$msg .= "into looking for edit flags $eol";
                while ( $iiStart !== false ) {
                    $cnt++;
                    if ( $cnt > 4000 ) {
                        throw new Exception( "$msg $errorBeg E#492 way to many iiStarts iiStart = $iiStart $errorEnd" );
                    }
                    $searchString = freewheeling_edit_setGlobals::editViewStart;
                    $iiStart = strpos( $html, $searchString );
                    if ( $iiStart === false ) {
                        if ( $debugResource )$msg .=
                            "not found '$searchString' length of html " . strlen( $html ) . $eol;
                        break;
                    }
                    if ( $debugResource )$msg .= "found one at $iiStart $eol";
                    $iiEnd = strpos( $html, freewheeling_edit_setGlobals::editViewEnd,
                        $iiStart );
                    if ( $iiEnd !== false ) {
                        $removed = substr( $html, $iiStart, $iiEnd - $iiStart );
                        if ( $iiEnd - $iiStart > 700 || $iiEnd >= strlen( $html ) - 30 ) {
                            throw new Exception( "$msg $errorBeg E#797 either end-start > 400 or iend is a tend of straing 
											iiStart is $iiStart, iiEnd is $iiEnd, length is " . strlen( $html ) . $errorEnd . substr( $removed, 0, 400 ) . $eol );
                        }
                    } else {
                        throw new Exception( "$msg $errorBeg E#798 no end to match start iiStart is $iiStart, iiEnd is $iiEnd, 
							length is " . strlen( $html ) . $errorEnd . substr( $removed, 0, 200 ) . $eol );
                    }
                    if ( $debugResource )$msg .= "before:" .
                    htmlspecialchars( substr( $html, $iiStart - 5, $iiEnd - $iiStart + 20 ) ) . $eol;
                    $html = substr( $html, 0, $iiStart ) . substr( $html, $iiEnd + 17 );
                    if ( $debugResource )$msg .= "after:" .
                    htmlspecialchars( substr( $html, $iiStart - 5, $iiEnd - $iiStart + 20 ) ) . $eol;
                } // end while
                if ( $debugResource )$msg .= ( "start of file:step 4 " . htmlspecialchars( substr( $html, 0, 30 ) ) . $eol );
            }
            if ( $debugResource ) print "<hr>$msg $eol<hr>";
        } // this code deals with login vs not login display 
        $nocopyrght = true;
        if ( $debugResource )$msg .= "start of file:step 5 " .
        htmlspecialchars( substr( $html, 0, 30 ) ) . $eol;
        if ( $nocopyrght )
            $msg .= $html;
        else
            $msg .= freewheel_encapsulateWithCopyright( $html );
        return $msg;
    } // end try
    catch ( Exception $e ) {
        $msg .= $e->getMessage() . $eol;
        return $msg;
    } // end catch
} //  end function freewheelingWriteUp( $attributeArray ) 
function freewheeling_removetags( $buffer ) {
    // remove the <  > items from the buffer
    global $eol, $errorBeg, $errorEnd;
    $debugremoveTag = false;
    $iiOpen = strpos( $buffer, "<" );
    $cnt = 0;
    while ( $iiOpen !== false ) {
        if ( $debugremoveTag ) print "freewheeling_removetags:before: 
                                    " . htmlspecialchars( $buffer ) . "<br/>";
        $cnt++;
        if ( $cnt > 50 )
            throw new Exception( "$errorBeg #E927 too many timea around remove tags                              $errorEnd" );
        $iiClose = strpos( $buffer, ">", $iiOpen );
        if ( $debugremoveTag ) print "freewheeling_removetags: open, close  $iiOpen, $iiClose<br/> ";
        if ( $iiClose === false ) {
            throw new Exception( "$errorBeg E#924 did not fnd cllosing > in html
                $errorEnd $iiOpen, $iiClose $eol 123456789012345678901234567890 $eol
                " . htmlspecialchars( $buffer ) . $eol );
        }
        $buffer = substr( $buffer, 0, $iiOpen ) . substr( $buffer, $iiClose + 1 );
        if ( $debugremoveTag ) print "freewheeling_removetags:after: " . htmlspecialchars( $buffer ) . "<br/>";
        $iiOpen = strpos( $buffer, "<" );
        if ( $debugremoveTag ) print "freewheeling_removetags: open  $iiOpen<br/> ";
    }
    return $buffer;
}
function freewheeling_removeTagItem( $buffer, $searchItem ) {
    // find the search item which is th e middle of a tag remove the tag
    // wf the tag is a <a then go aftr the <.a> as well
    // iimiddle points to the middle of an html tag
    // find the two ends  <  > and remove all between
    global $eol, $errorBeg, $errorEnd;
    $msg = "";
    $debugRemove = false;
    $iimiddle = strpos( $buffer, $searchItem );
    $cntFound = 0;
    while ( $iimiddle > 1 ) {
        $cntFound++;
        if ( $cntFound > 10 )
            throw new Exception( "$msg $errorBeg E#838 too many tags found $errorEnd" );
        $iiEnd = strpos( $buffer, ">", $iimiddle );
        $iiStart = strrpos( $buffer, "<", $iimiddle - strlen( $buffer ) );
        if ( $debugRemove ) {
            $msg .= "removeTagItem:firstLook:$iiStart:$iiEnd: $eol";
            //      return array($msg,$buffer);
        }
        if ( $debugRemove ) print "   if ( $iiEnd - $iiStart > 100 || $iiEnd < $iiStart ) ";
        if ( $iiEnd - $iiStart > 130 || $iiEnd < $iiStart ) {
            print( "iiEnd == $iiEnd" );
            print( "iiStart == $iiStart" );
            $temp2 = $iiEnd - $iiStart;
            print( "iiEnd - iiStart == $temp2" );
            $temp = "$msg  $errorBeg E#839 range to long in  
                                freewheeling_removeTagItem $temp2 greater than 100";
            $x = 1;
            print $temp
                . substr( $buffer, $iiStart - 20, 80 ) . $errorEnd;
            throw new Exception( $temp );
        }
        if ( $debugRemove )$msg .= "removeTagItem:before:$iiStart:$iiEnd: " .
        htmlspecialchars( substr( $buffer, $iiStart - 5, $iiEnd - $iiStart + 20 ) ) . $eol;
        $removeThing1 = substr( $buffer, $iiStart, 2 );
        $removeThing2 = substr( $buffer, $iiStart, 10 );
        $buffer = substr( $buffer, 0, $iiStart ) . substr( $buffer, $iiEnd + 1 );
        if ( $debugRemove )$msg .= "removeTagItem:after:$iiStart:$iiEnd: " .
        htmlspecialchars( substr( $buffer, $iiStart - 5, $iiEnd - $iiStart + 20 ) ) . $eol;
        if ( $removeThing1 == "<a" ) {
            $iiStart = strpos( $buffer, "<", $iiStart );
            $iiEnd = strpos( $buffer, ">", $iiStart );
            $buffer = substr( $buffer, 0, $iiStart ) . substr( $buffer, $iiEnd + 1 );
        }
        if ( $removeThing2 == "<!-- start" ) {
            $iiEnd = strpos( $buffer, "<-- end", $iiStart );
            $iiEnd = strpos( $buffer, ">", $iiEnd );
            $buffer = substr( $buffer, 0, $iiStart ) . Mid( $buffer, $iiEnd + 1 );
        }
        if ( $debugRemove )$msg .= "removeTagItem:final:$iiStart:$iiEnd: " .
        htmlspecialchars( substr( $buffer, $iiStart - 5, $iiEnd - $iiStart + 20 ) ) . $eol;
        if ( $debugRemove )$msg .= $eol;
        $iimiddle = strpos( $buffer, $searchItem, $iiEnd );
    }
    return array( $msg, $buffer );
} // End Function freewheeling_removeTagItem
function freewheel_encapsulateWithCopyright( $html ) {
    global $freewheeling_pagesPreBuiltUrl;
    $msg = freewheeling_edit_setGlobals::freewheelingEditSetGobals( "freewheel_encapsulateWithCopyright " );
    $temp = "
<table>
    <tr>
        <td>
        <td class='rrw_trail_copyrightOnSides'>
            <img src='$freewheeling_pagesPreBuiltUrl/images/left%20margin.jpg' alt='copyright &copy;2019 Shaw-Weil associates' /></td>
        <td>
            $html
        </td>
        <td class='rrw_trail_copyrightOnSides'>
            <img src='$freewheeling_pagesPreBuiltUrl/images/left%20margin.jpg' alt='copyright &copy;2019 Shaw-Weil associates' /></td>
    </tr>
</table>";
    return $temp;
} // end freewheel_encapsulateWithCopyright ($html)
class freewheeling_edit_setGlobals {
    const editViewStart = "<!-- start wipe -->";
    const editViewEnd = "<!-- end wipe -->";
    const latRoundTo = 7;
    static public
    function freewheeling_map_setconstants() {
        global $eol, $errorBeg, $errorEnd;
        global $meters2Miles, $feet2Miles, $freewheelingeasy_feets2Meter;
        global $rrw_pagesPreBuiltDire, $freewheeling_pagesPreBuiltUrl;
        global $freewheeling_pgherie;
        global $freewheel_degrees2Meters;
        global $freewheelingaasy_editsite_url;
        global $freewheelingeasy_images_URL;
        global $freewheelingeasy_kml_directory;
        global $freewheelingeasy_latLngTolerance;
        global $freewheelingeasy_okay;
        global $freewheelingeasy_penciliconimg;
        global $freewheelingeasy_stylesheet;
        global $freewheelingeasy_latRoundTo;
        $msg = "";
        // temp variables
        $debugHost = false;
        $prebuiltDireName = "prebuilt";
        $host = get_site_url();
        if ( $debugHost ) print "host is $host $eol ";
        if ( strpos( $host, "edit.shaw" ) !== false ) {
            $freewheeling_pagesPreBuiltUrl = "https://dev.freewheelingeasy.com/$prebuiltDireName";
            $freewheeling_pgherie = "https://edit.shaw-weil.com/";
            $rrw_pagesPreBuiltDire = "/home/pillowan/www-freewheel-dev/$prebuiltDireName";
        } else {
            $freewheeling_pagesPreBuiltUrl = "https://freewheelingeasy.com/$prebuiltDireName";
            $freewheeling_pgherie = "https://freewheelingeasy.com/";
            $rrw_pagesPreBuiltDire = "/home/pillowan/www-freewheelingeasy/$prebuiltDireName";
        }
        if ( $debugHost ) print "$freewheeling_pagesPreBuiltUrl $eol $rrw_pagesPreBuiltDire $eol";
        $uploadInfo = wp_upload_dir();
        // formating variables used everywhere
        $eol = "<br />\n";
        $errorBeg = "$eol<font color='red'>";
        $errorEnd = "</font>$eol$eol";
        // distance constants
        $meters2Miles = 1609.334;
        $feet2Miles = 5280;
        // in this area .00003 is about .18 ft NS by .135 Ft SW ie less than 2 inches
        $freewheel_degrees2Meters = 140025;
        $freewheelingeasy_editsite_url = "https://edit.shaw-weil.com/edit-icon/";
        $freewheelingeasy_feets2Meter = 3.28008399;
        $freewheelingeasy_images_URL = "$freewheeling_pagesPreBuiltUrl/images";
        $freewheelingeasy_kml_directory = $uploadInfo[ "basedir" ] . "/kml";
        $freewheelingeasy_okay = "<span style='color:green;'> okay</span><br />\n";
        $freewheelingeasy_latLngTolerance = .00002;
        $freewheelingeasy_latRoundTo = 7;
        $freewheelingeasy_penciliconimg = "<img alt='Edit ' 
				src='$freewheelingeasy_images_URL/penicon.gif' />";
        $freewheelingeasy_stylesheet = "
<link rel='stylesheet' type='text/css' media='all' href='$freewheeling_pagesPreBuiltUrl/amen.css' />";
    }
    static public
    function freewheelingEditSetGobals( $from = "not sprecfied" ) {
        global $eol, $errorBeg, $errorEnd;
        global $rrw_access, $rrw_codes, $rrw_codes_amenities, $rrw_codes_surface;
        global $rrw_bizaccsvc, $rrw_businessservices;
        global $rrw_trailicons, $rrw_trailiconsall, $rrw_traillines;
        global $rrw_icons, $rrw_lines, $rrw_towns, $rrw_regions, $rrw_exceptions, $rrw_trails;
        global $wpdbExtra, $rrw_googlebilling, $rrw_history, $rrw_business, $rrw_services, $rrw_products;
        global $rrw_trail_routes, $rrw_segments, $rrw_trail_mile, $rrw_trail_rating_set,
        $rrw_route_changes, $rrw_urllist, $rrw_force_access, $rrw_mileposts;
        $msg = "";
        if ( false )$msg .= "Set Globals from $from $eol";
        $msg .= freewheeling_edit_setGlobals::freewheeling_map_setconstants();
        // $wpdbExtra = new wpdbExtra();
        $wpdbExtra->show_errors();
        // prefix 0 implies segment data
        $rrw_access = $wpdbExtra->prefix . "1rrw_access";
        $rrw_business = $wpdbExtra->prefix . "1rrw_business";
        $rrw_codes = $wpdbExtra->prefix . "0rrw_codes";
        $rrw_force_access = $wpdbExtra->prefix . "0rrw_force_access";
        $rrw_googlebilling = $wpdbExtra->prefix . "0rrw_googlebilling";
        $rrw_history = $wpdbExtra->prefix . "1rrw_history";
        $rrw_icons = $wpdbExtra->prefix . "00rrw_icons";
        $rrw_lines = $wpdbExtra->prefix . "00rrw_lines";
        $rrw_products = $wpdbExtra->prefix . "3rrw_products";
        $rrw_towns = $wpdbExtra->prefix . "00rrw_towns";
        $rrw_trail_mile = $wpdbExtra->prefix . "4rrw_trail_mile";
        $rrw_trail_rating_set = $wpdbExtra->prefix . "0rrw_trail_rating_set";
        $rrw_trail_routes = $wpdbExtra->prefix . "0rrw_trail_routes";
        $rrw_trails = $wpdbExtra->prefix . "00rrw_trails";
        $rrw_route_changes = $wpdbExtra->prefix . "0rrw_route_changes";
        $rrw_segments = $wpdbExtra->prefix . "0rrw_segments";
        // if (substr($from,0,1) != "x")
        // print " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ---From $from - set rrw_segments to $rrw_segments <br />";
        // prefix 1 implies basic business data
        $rrw_services = $wpdbExtra->prefix . "1rrw_services";
        // prefix 2 implies combinations of location and business data
        $rrw_urllist = $wpdbExtra->prefix . "2rrw_urllist";
        // prefix 3 implies location data derived from kml files
        $rrw_mileposts = $wpdbExtra->prefix . "4rrw_mileposts";
        $rrw_regions = $wpdbExtra->prefix . "00rrw_regions";
        $rrw_exceptions = $wpdbExtra->prefix . "0rrw_exceptions";
        // prefix 07 implies a sql view
        $rrw_trailicons = $wpdbExtra->prefix . "7rrw_trailicons"; // view
        $rrw_trailiconsall = $wpdbExtra->prefix . "7rrw_trailiconsall"; // view
        $rrw_traillines = $wpdbExtra->prefix . "7rrw_traillines"; //view
        $rrw_codes_amenities = "07rrw_codes_amenities";
        $rrw_businessservices = $wpdbExtra->prefix . "7rrw_businessservices";
        $rrw_codes_surface = "07rrw_codes_surface";
        // prefix 4 implies a temporaty table. recreated before each use
        $rrw_bizaccsvc = $wpdbExtra->prefix . "4rrw_bizaccsvc";
        return $msg;
    }
    static public
    function getAPIkey( $task = "" ) {
        return freewheelAPI::getAPIkey( $task );
    }
    static public function getBillingName( $apiKey ) {
        return freewheelAPI::getBillingName( $apiKey );
    }
    static public function rrw_updateTheMenu() {
        $msg = "";
        $thisAmen = get_user_option( "rrw_amenitytrail" );
        $thisAmenPlus = str_replace( " ", "+", $thisAmen );
        $msg .= "
<script>
    var element = document.getElementById( 'menu-menu-1' );
    var text1 = element.innerHTML;
    var text2 = text1.replace( 'Belmar', '$thisAmenPlus' );
    var text3 = text2.replace( 'Belmar', '$thisAmen ' );
    element.innerHTML = text3;
</script>
";
        return $msg;
    }
    public static function formatCommentsOnly() {
        $msg = "<h1 style='color:red' > You have been taken to an special 
            website for editing the data</h1><br />Please enter a comment in the field
            about the change that you think should be made. Someone will review. If they
            imap_rfc822_write_address we will send the result of that review.";
        return $msg;
    }
    public static
    function notAllowedToEdit( $what = "", $trailid = "" ) {
        $allowed = freewheeling_edit_setGlobals::allowedToEdit( $what, $trailid );
        return !$allowed;
    }
    public static function allowedToComment() {
        return false;
    }
    public static function allowedToEdit( $what = "", $trailid = "" ) {
        global $eol, $errorBeg, $errorEnd;
        $debug = false;
        // what     message to the user before the login ib form
        // trailid  does user have privledge to edit this trail (future)
        if ( $debug ) print "allowedToEdit( $what , $trailid ) $eol";
        $allowed = rrwUtil::fetchParameterString( "allowed" );
        if ( $debug ) print 'current_user_can( "edit_posts" )' .
        current_user_can( "edit_posts" ) . $eol;
        if ( current_user_can( "edit_posts" ) )
            return true;
        elseif ( freewheeling_edit_setGlobals::isAllowedToEditAny() )
        return true;
        elseif ( !empty( $allowed ) )
        return true;
        if ( empty( $what ) )
            return false;
        print freewheeling_edit_setGlobals::showLoginform( $what );
        return false;
    }
    public static function showLoginform( $msg1 ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $passhelp = "I do not know my password,
                <a href='https://edit.shaw-weil.com/wp-login.php?action=lostpassword'>
                    help me change it</a> ";
        $msg = "$errorBeg you need permission to $msg1 $errorEnd $passhelp or login$eol$eol";
        $msg .= "<div class='wp_login_form'>";
        $args = array(
            'echo' => false,
            'label_username' => __( 'Email Address' ),
        );
        $msg .= wp_login_form( $args );
        $msg .= $passhelp;
        return $msg;
    }
    static public function isAllowedToEditAny() {
        if ( current_user_can( "administrator" ) )
            return true;
        else
            return false;
    }
    static public function pushToServer( $fileName, $fileContents ) {
        return freewheelingeasy_calculateMP::pushToPrebuiltPages( $fileName, $fileContents, false );
    }
    static public
    function readFromServer( $fileName, & $fileContents ) {
        global $eol, $errorBeg, $errorEnd;
        global $rrw_pagesPreBuiltDire;
        $msg = "";
        $fileNameFull = "$rrw_pagesPreBuiltDire/$fileName";
        if ( !file_exists( $fileNameFull ) ) {
            $msg .= formatBacktrace( "$errorBeg E#669 looking for file $fileNameFull- it does not exist $errorEnd" );
            throw new Exception( $msg );
        }
        $fileContents = file_get_contents( $fileNameFull );
        return $msg;
    }
    static public
    function getTrailInfo( $trailid ) {
        // trailid      maybe trailid
        //              may have slashs (/) which re remove
        //              may have minus which re remov
        // trailidlower make it all lower case
        global $eol, $errorBeg, $errorEnd;
        global $freewheeling_pagesPreBuiltUrl;
        $debugGetTrail = false;
        try {
            if ( $debugGetTrail ) print "getTrailInfo( $trailid ) $eol";
            if ( !empty( $trailid ) ) {
                $trailid = trim( stripslashes( $trailid ) );
                $trailidLower = strtolower( $trailid );
                $trailidNoMinus = str_replace( "-", " ", $trailid );
                $toctableFile = "$freewheeling_pagesPreBuiltUrl/data/toctable.json";
                if ( $debugGetTrail ) print $toctableFile;
                $toctableOutput = file_get_contents( $toctableFile );
                $trailInfo = json_decode( $toctableOutput, true );
                if ( !is_array( $trailInfo ) )
                    throw new Exception( "E#692 Json decode did not make an array $eol $toctableOutput" );
                // is it the exact trail id
                if ( array_key_exists( $trailidLower, $trailInfo ) ) {
                    if ( $debugGetTrail ) print "Found an exact match to trailid";
                    return $trailInfo[ $trailidLower ];
                }
                // not the trail id, maybe the trail name
                foreach ( $trailInfo as $key => $trail ) {
                    if ( strcasecmp( $trail[ "trname" ], $trailid ) == 0 )
                        return $trailInfo[ $key ];
                    if ( strcasecmp( $trail[ "trHtmlfileName" ], $trailid ) == 0 )
                        return $trailInfo[ $key ];
                    if ( strcasecmp( $trail[ "trHtmlfileName" ], $trailidNoMinus ) == 0 )
                        return $trailInfo[ $key ];
                }
                if ( $debugGetTrail ) {
                    print "$errorBeg E#522 No match in the table $errorEnd <table>";
                    foreach ( $trailInfo as $key => $trail ) {
                        print rrwFormat::cellRow( $trail[ "trname" ], $trail[ "trHtmlfileName" ] );
                    }
                    print "</table>";
                }
                foreach ( $trailInfo as $key => $trail ) {
                    if ( strncasecmp( $trail[ "trname" ], $trailid, strlen( $trailid ) ) == 0 )
                        return $trailInfo[ $key ];
                }
            }
            if ( $debugGetTrail ) print "No match found in toctable $eol";
            $trailinfo = freewheeling_edit_setGlobals::trailInfoEmpty( "", "Western PA and Eastern Ohio Trails" );
            return $trailinfo;
        } catch ( Exceptio $e ) {
            throw new Exception( "$errorBeg E#658" . $e->getMessage() . $errorEnd );
        }
    }
    public static
    function trailInfoEmpty( $trid = 'no trail id', $trname = 'no trail name' ) {
        $temp = array( "north" => "42.6", "south" => "38.76", "east" => "-75.8", "west" => "-81.8",
            "trname" => $trname, "trailid" => $trid, "labelNorth" => 39, "labelEast" => -77.5, "sqlWhere" => "trailid = '$trid'" );
        return $temp;
    }
} // end class freewheeling_edit_setGlobals
?>