<?php

require_once "rrw_html_extractTemp.php";


class dinomitedys_make_html {
    const rrw_dinomites = "wpprrj_00rrwdinos";
    const baseDire = "/home/pillowan/www-dinomitedays";
    const design_images_dire = self::baseDire . "/designs/images";


    public static function make_html_files( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "update some pages $eol";
        ini_set( "display_errore", true );
        try {
            $msg .= self::updateLocatonMap();
            $msg .= self::detailPageLocation();
        } catch ( Exception $ex ) {
            $msg .= "E#400 xxx catch " . $ex->getMessage();
        }
        return $msg;
    }

    static function updateLocatonMap() {
        global $wpdb;
        global $eol, $errorBeg, $errorEnd;
        $msg = "";

        $ch = curl_init( "https://edit.shaw-weil.com/make-dino-map-files/?switch=clean" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        $msg .= "$result $eol";
        curl_close( $ch );

        $from = "/home/pillowan/www-freewheel-dev/prebuilt/data/dinomites.txt";
        $to = "/home/pillowan/www-freewheelingeasy/prebuilt/data/dinomites.txt";

        $msg .= "copy ($from, $to); $eol";
        $result = copy( $from, $to );
        if ( false === $result )
            throw new Exception( "$msg $errorBeg E#842 copy failed $errorEnd" );
        $msg .= "copy worked $eol";

        return $msg;
    }

    public static function UpdateImages( $dino ) {
        global $wpdbExtra, $rrw_dino;
        global $eol, $errorBeg, $errorEnd;
        $dirDesign = "/home/pillowan/www-dinomitedays/designs";
        $msg = "";
        $filename = "$dirDesign/$dino.htm";
        $buffer = file_get_contents( $filename );
        $buffer = self::findPlace( $buffer );
        // build the insert
        $newdiv = '</table><br>' .
        dinomitedys_upload::displayExisting( $dino, false ) .
        "\n<table>\n";
        //  insert and write
        //      $newdiv = str_replace("270","150", $newdiv);
        if ( false === strpos( $buffer, "xxzzy" ) )
            throw new Exception( "$msg $errorBeg #791 buffer does 
                    not contain xxzzy $errorEnd" );
        if ( strpos( $buffer, "xxzzy" ) < 500 )
            throw new Exception( "$msg $errorBeg #791 xxzzy to close 
                    to frnt of buffer $errorEnd" );

        $buffer = str_replace( "xxzzy", $newdiv, $buffer );
        $filenameNew = str_replace( "$dino", "$dino-new", $filename );
        $fp = fopen( $filenameNew, "w" );
        fwrite( $fp, $buffer );
        fclose( $fp );
        $msg .= " an  updated verson of <a href='/designs/$dino-new.htm' 
                target='new' > $dino-new.htm is here</a>. 
                Check it out. Do NOT forget to refreash. If okay 
                <a href='/fixit/?task=renamenewdino&dino=$dino' target='new'>
                move to production</a>$eol";
        return $msg;
    }

    private static function findPlace( $buffer ) {
        //work to find the right place to insert the images
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debug = true;
        // first try a previous insertion
        $iiDiv = strpos( $buffer, "<br><div id='dinoImages" );
        if ( false !== $iiDiv ) {
            if ( $debug ) print "findPlace:previous insert $eol";
            $iiDivEnd = strpos( $buffer, "end dinoImages", $iiDiv );
            if ( false === $iiDivEnd )
                throw new Exception( "$msg $errorBeg 
                        E#762 did not find end dnImages' $errorEnd" );
            $iiDivEnd = strpos( $buffer, ">", $iiDivEnd ) + 1;
            if ( $debug ) print "get buffer to $iiDiv, then from $iiDivEnd $eol";
            $buffer = substr( $buffer, 0, $iiDiv ) . "xxzzy" .
            substr( $buffer, $iiDivEnd );
            return $buffer;
        }
        // not previous, try the origianal insertion
        $iiDiv = strpos( $buffer, "thumbnails for more" );
        if ( false !== $iiDiv ) {
            if ( $debug ) print "findPlace:has pictures insert $eol";
            $iiStart = $iiDiv - strlen( $buffer );
            $iiDiv = strrpos( $buffer, "<p", $iiStart );
            $iiDivEnd = strpos( $buffer, "</table", $iiDiv );
            if ( false === $iiDivEnd )
                throw new Exception( "$msg $errorBeg E#773 missing </table $errorEnd" );
            $iiDivEnd = $iiDivEnd + 14;
            $buffer = substr( $buffer, 0, $iiDiv ) . "xxzzy" .
            substr( $buffer, $iiDivEnd );
            return $buffer;
        }
        // not previous, nor orginal, try working up from footer
        $iiFoot = strpos( $buffer, '<div id="dinofooter' );
        if ( false !== $iiFoot ) {
            if ( $debug ) print "findPlace:up from footer insert $eol";
            $iiStart = $iiFoot - strlen( $buffer );
            $iiDivEnd = strrpos( $buffer, "<tr", $iiStart );
            if ( false === $iiDivEnd )
                throw new Exception( "$msg $errorBeg E#782 missing 
                            &lt;tr starting at $iiFoot ($iiStart) $errorEnd" );
            $iiStart = $iiDivEnd - strlen( $buffer ) - 3;
            $iiDiv = strrpos( $buffer, "<tr", $iiStart );
            $dist = $iiStart - $iiDiv;
            if ( 200 < $dist )
                throw new Exception( "$msg $errorBeg E#788 distance between
                start and finish ($dist) > 200 $errorEnd" );
            $buffer = substr( $buffer, 0, $iiDiv ) . "xxzzy" .
            substr( $buffer, $iiDivEnd );
            return $buffer;
        }
        // I give up, the templat file was not followed
        return "$msg $errorBeg E#769 did not find a place to
                insert  the images $errorEnd";

    }
    static public function detailPageLocation($filename = "%") {
        global $wpdb;
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debug = false;
        ini_set( "display_errors", true );
        error_reporting( E_ALL | E_STRICT );
        try {
            $sql = "select filename, mapLoc, mapDate, latitude, longitude from 
                    " . self::rrw_dinomites . "
                        where filename = '$filename'                     
                        order by filename ";
            $pages = $wpdb->get_results( $sql, ARRAY_A );
            foreach ( $pages as $page ) {
                $file = $page[ "filename" ];
                $mapLoc = $page[ "mapLoc" ];
                $mapDate = $page[ "mapDate" ];
                $latitude = $page[ "latitude" ];
                $longitude = $page[ "longitude" ];
                $filenameFull = self::baseDire . "/designs/$file.htm";
                $SeenDate = new DateTime( $mapDate );
                if ( $SeenDate > new DateTime( "2020-01-01" ) )
                    $displayDate = $SeenDate->format( "Y-M" );
                else
                    $displayDate = $SeenDate->format( "Y" );
                $content = "";

                $cntOriginal = rrwParse::loadBufferWithFile( $filenameFull );
                list( $msgTemp, $outcontent ) = rrwParse::extractTo( "Fossil Location" );
                $msg .= $msgTemp;
                $content .= $outcontent .
                "Fossil Location:</b></i></font> $mapLoc ($displayDate) 
                    <a href='https://dinomitedays.org/map/?dino=true" .
                "&latitude=$latitude&longitude=$longitude' > map </a>";
                $msg .= rrwParse::trimTo( "<br" );
                list( $msgTemp, $ountententRest ) = rrwParse::extractTo( "</html>" );
                $content .= $ountententRest . "</html>";
                $cntFinal = strlen( $content );
                $tolerence = 120;
                if ( ( $cntOriginal - $cntFinal ) > $tolerence )
                    throw new Exception( "$msg $errorBeg E#836 original is $cntOriginal long,
                            final is $cntFinal long, more then $tolerence $errorEnd" );
                $fpOut = fopen( "$filenameFull", "w" );
                fwrite( $fpOut, $content );
                fclose( $fpOut );
                $msg .= "Updated $filenameFull <a href='/designs/$file.htm' target='final'> $file.htm
                            </a> $eol ";
            } // end of each page/file
        } catch ( Exception $ex ) {
            $msg .= "E#400 xxx catch while processnig <a href='/designs/$file.htm' target='final'> 
                    $file.htm </a> $eol" . $ex->getMessage();;
        }

        return $msg;
    } // end  geocoded

    public static function updateFosilLocation( $filenameFull, $maploc,
        $latitude, $logitude ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debug = true;

        $content = "";
        $cntOriginal = rrwParse::loadBufferWithFile( $filenameFull );
        list( $msgTemp, $outcontent ) = rrwParse::extractTo( "Fossil Location" );
        $msg .= $msgTemp;
        $content .= $outcontent .
        "Fossil Location:</b></i></font> $mapLoc ($displayDate) 
                    <a href='https://dinomitedays.org/map/?dino=true" .
        "&latitude=$latitude&longitude=$longitude' > map </a>";
        $msg .= rrwParse::trimTo( "<br" );
        list( $msgTemp, $ountententRest ) = rrwParse::extractTo( "</html>" );
        $content .= $ountententRest . "</html>";
        $cntFinal = strlen( $content );
        $fpOut = fopen( "$filenameFull", "w" );
        fwrite( $fpOut, $content );
        fclose( $fpOut );
        $msg .= "Updated $filenameFull <a href='/designs/$file.htm' target='final'> $file.htm </a> $eol ";
        return $msg;
    }

    public static function findRelated( $dino, $withDefaults = true ) {
        // returns a list of filename that aresub pistures for a dino

        $debug = false;
        $dire = ABSPATH . "/designs/images";

        $numChars = strlen( $dino );
        $list = array();
        foreach ( new DirectoryIterator( $dire ) as $fileInfo ) {
            $entry = $fileInfo->getFilename();
            if ( strncasecmp( $dino, $entry, $numChars ) != 0 )
                continue;
            if ( strpos( $entry, "LCK" ) !== false )
                continue;
            if ( strpos( $entry, "_th." ) !== false )
                continue;
            $list[ "$entry" ] = 1;
        }
        $pics = array(
            "$dino.jpg" => 1,
            "$dino" . "_pic.jpg" => 1,
            "$dino" . "_sm.jpg" => 1,
        );
        if ( $debug ) print rrwUtil::print_r( $list, true, "list before remove three photos" );
        if ( $debug ) print rrwUtil::print_r( $pics, true, "photos to remove remove three photos" );
        foreach ( $pics as $pic => $dummy ) {
            if ( array_key_exists( $pic, $list ) )
                unset( $list[ $pic ] );
        }
        if ( $debug ) print rrwUtil::print_r( $list, true, "list after remove three photos" );
        ksort( $list );
        if ( $withDefaults )
            $list = array_merge( $pics, $list );
        return $list;
    } // end findRelated

} // end class findRelated
?>