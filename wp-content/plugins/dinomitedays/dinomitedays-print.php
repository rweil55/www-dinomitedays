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
            error_reporting(E_ALL);
            $msg = "";

            $howMany = rrwParam::Number("howMany", $attr, 1);
            $startAt = rrwParam::Number("startAt", $attr, 0);
            $msg = "How many: $howMany, start at: $startAt $eol";
            $sql = "select keyId,  name, status, filename, mapDate,
                    mapLoc, latitude, longitude
                    from " .  self::rrw_dinos .
                " order by name";
            if ($debugLast) $msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA($sql);
            if ($debugLast) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $msg .= "<table>";
            $cnt = 0;
            $displayed = 0;
            foreach ($recs as $rec) {
                // $msg .= " if ($startAt > $cnt) { $eol";
                $cnt++;
                if ($startAt > $cnt) {
                    continue; // skip until we reach the startAt
                }
                $name = $rec["name"];
                $status = $rec["status"];
                $filename = $rec["filename"];
                $mapDate = $rec["mapDate"];
                $mapLoc = $rec["mapLoc"];
                $latitude = $rec["latitude"];
                $longitude = $rec["longitude"];
                $keyId = $rec["keyId"];

                $msg .= rrwFormat::CellRow($name, $mapDate, $mapLoc, $latitude, $longitude, $keyId);


                $displayed++;
                if ($displayed >= $howMany) {
                    break; // stop after howMany
                }
            }
            $msg .= "</table>";
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#1333 dinomitedAys_upload:upload: $errorEnd");
        }
        return $msg;
    } //  end function print xxx

} // end class
