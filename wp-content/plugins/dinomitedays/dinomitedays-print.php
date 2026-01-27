<?php
class dinomitedays_print
{
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

            $howMany = rrwParam::Number("howMany", $attr, 120);
            $startAt = rrwParam::Number("startAt", $attr, 0);
            $msg = "How many: $howMany, start at: $startAt $eol";
            $sql = "select keyId,  DinoName, status, fileName, mapDate,
                        mapLoc, latitude, longitude
                    from $wpdbExtra->dinosaurs
                    order by dinoName";
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
                $name = $rec["DinoName"];
                $status = $rec["status"];
                $fileName = $rec["fileName"];
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
            throw new Exception("$msg $errorBeg E#1333 dinomitedAys:print: $errorEnd");
        }
        return $msg;
    } //  end function print xxx

} // end class
