<?php

require_once "rrw_html_extractTemp.php";


class dinomitedys_make_html_class {
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

    static private function detailPageLocation() {
        global $wpdb;
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debug = true;
        ini_set( "display_errors", true );
        error_reporting( E_ALL | E_STRICT );
        try {
            $sql = "select filename, mapLoc, mapDate, latitude, longitude from 
                    " . self::rrw_dinomites . " 
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

    public static function findRelated( $dino ) {
        // returns a list of filename that aresub pistures for a dino
        $dire = ABSPATH . "/designs/images";

        $numChars = strlen( $dino );
        $list = array();
        foreach ( new DirectoryIterator( $dire ) as $fileInfo ) {
            $entry = $fileInfo->getFilename();
            if ( strncasecmp( $dino, $entry, $numChars ) != 0 )
                continue;
            if (strpos($entry,"LCK") !== false)
                continue;
           if (strpos($entry,"_th.") !== false)
                continue;
            $list[ "$entry" ] = 1;
        }
        ksort($list);
        return $list;
    } // end findRelated

} // end class
?>