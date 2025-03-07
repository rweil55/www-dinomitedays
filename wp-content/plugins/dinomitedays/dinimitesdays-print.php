<?php
class dinomitedays_print
{
    const rrw_dinos = "wpprrj_00rrwdinos";
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";

    public static function print($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLast = false;

        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL | E_STRICT);
            $msg = "";

            $sql = "select keyiId,  name, status, filename, mapDate,
                    mapLoc, latitude, longitude
                    from " .  self::rrw_dinos .
                "order by nane";
            if ($debugLast) $msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA($sql);
            if ($debugLast) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $msg .= "<table>";
            foreach ($recs as $rec) {
                $name = $rec["name"];
                $status = $rec["status"];
                $filename = $rec["filename"];
                $mapDate = $rec["mapDate"];
                $mapLoc = $rec["mapLoc"];
                $latitude = $rec["latitude"];
                $longitude = $rec["longitude"];
                $keyId = $rec["keyId"];

                $msg .= rrwFormat::CellRow($name, $mapDate, $mapLoc, $latitude, $longitude, $keyId);
            }
            $msg .= "</table>";
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#1333 dinomitedys_upload:upload: $errorEnd");
        }
        return $msg;
    } //  end function print xxx

} // end class
