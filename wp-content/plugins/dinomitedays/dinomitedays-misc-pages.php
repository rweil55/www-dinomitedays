<?php

ini_set( "display_errors", true );
error_reporting( E_ALL | E_STRICT );

class dinomitedays_misc_pages {
    const rrw_dinos = "wpprrj_00rrwdinos";
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";

    public static function last_seen( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLast = false;

        try {
            ini_set( "display_errors", true );
            error_reporting( E_ALL | E_STRICT );
            $msg = "";

            $sql = "select name, status, filename, mapdate, 
                    maploc, latitude, longitude 
                    from " .  self::rrw_dinos . " order by year(mapDate) ";
            $sql .= " desc ";
            $sql .= ", name asc ";
            if ( $debugLast )$msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA( $sql );
            if ( $debugLast )$msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $yearPast = "not yet";
            foreach ( $recs as $rec ) {
                $name = $rec[ "name" ];
                $status = $rec[ "status" ];
                $filename = $rec[ "filename" ];
                $mapdate = $rec[ "mapdate" ];
                $maploc = $rec[ "maploc" ];
                $latitude = $rec[ "latitude" ];
                $longitude = $rec[ "longitude" ];

                $mapYear = new DateTime( $mapdate );
                $mapYear = $mapYear->format( "Y" );
                if ( $mapYear != $yearPast )
                    $msg .= "<span style='font-weight:bold; ' > $mapYear </span>$eol";
                $yearPast = $mapYear;
                $displayName = $name;
                if ( !empty( $status ) )
                    $displayName .= " $status ";
                if ( 0 == $latitude )
                    $displayMap = ""; 
                else
                    $displayMap = "$maploc
                    <a href='/map/?dino=true&latitude=$latitude&longitude=$longitude' > map</a>";
                $msg .= "<a href='/designs/$filename.htm' > $displayName</a>
                                    $displayMap $eol";
            }

        } catch ( Exception $ex ) {
            throw new Exception( "$msg $errorBeg E#763 dinomitedys_upload:upload: $errorEnd" );
        }
        return $msg;
    } // end last_seen
} // end class
