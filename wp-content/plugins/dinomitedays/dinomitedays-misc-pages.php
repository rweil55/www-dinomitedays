<?php

ini_set("display_errors", true);
error_reporting(E_ALL);

class dinomitedays_misc_pages
{
    const rrw_dinos = "wpprrj_00rrwdinos";
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";

    public static function last_seen($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLast = false;

        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL);
            $msg = "";
            $lastOrkey = rrwParam::String("lastorkey", $attr);
            if (strcmp("key", $lastOrkey) == 0)
                $numberorder = true;
            else
                $numberorder = false;
            if ($debugLast) $msg .= "lastorkey: $lastOrkey, value = $numberorder $eol";
            $sql = "select keyid,  name, status, filename, mapdate,
                    maploc, latitude, longitude
                    from " .  self::rrw_dinos;
            if ($numberorder)
                $sql .= " order by keyid";
            else
                $sql .= " order by year(mapDate)  desc, name asc ";
            if ($debugLast) $msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA($sql);
            if ($debugLast) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $yearPast = "not yet";
            foreach ($recs as $rec) {
                $name = $rec["name"];
                $status = $rec["status"];
                $filename = $rec["filename"];
                $mapdate = $rec["mapdate"];
                $maploc = $rec["maploc"];
                $latitude = $rec["latitude"];
                $longitude = $rec["longitude"];
                $keyid = $rec["keyid"];

                $mapYear = new DateTime($mapdate);
                $mapYear = $mapYear->format("Y");
                if ($mapYear != $yearPast && !$numberorder)
                    $msg .= "<span style='font-weight:bold; ' > $mapYear </span>$eol";
                $yearPast = $mapYear;
                if ($numberorder)
                    $displayName = "#$keyid $mapYear $name";
                else
                    $displayName = "$name";
                if (!empty($status))
                    $displayName .= " - $status ";
                if (0 == $latitude)
                    $displayMap = "";
                else
                    $displayMap = "$maploc
                    <a href='/map/?dino=true&latitude=$latitude&longitude=$longitude' > map</a>";
                $msg .= "<a href='/designs/$filename.htm' > $displayName</a>
                                    $displayMap $eol";
            }
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#1333 dinomitedys_upload:upload: $errorEnd");
        }
        return $msg;
    } // end last_seen

    public static function knownLocation($attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLoc = false;

        $sql = "select keyId,  name, status, filename, mapDate,
                    mapLoc, latitude, longitude
                    from " .  self::rrw_dinos .
            " where status = '' and latitude > 0
                     order by mapLoc, name limit 50";

        if ($debugLoc) $msg .= "$sql $eol";
        $recs = $wpdbExtra->get_resultsA($sql);
        if ($debugLoc) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

        $msgLeft = "<table border='0' >\n";
        $msgLeft .= rrwFormat::CellHeaderSize(15, 40);
        $cnt = 0;
        foreach ($recs as $rec) {
            $cnt++;
            if ($cnt > 38) break;
            $mapDate = $rec["mapDate"];
            $filename = $rec["filename"];
            if ($mapDate > 2023)
                $mapDate = "Recently";
            $mapDateDisplay = "<a href='/upd/?dino=$filename' target='update' >$mapDate</a> \n";
            $mapLoc = $rec["mapLoc"];
            $msgLeft .= rrwFormat::CellRow($mapDateDisplay, $mapLoc);
        }
        $msgLeft .= "</table>";

        $msgRight = "I am trying to locate all the Carnegie History Center's 100 or so dinosaurs
        that were placed around the city in 2003.  I have found about 25 of them but most have disappeared.</p><p>
        This is a list of where I have found those.  </p><p>
        if you see one someplace else please let me know: You can email me at locate@dinomitedays.org or call 412-530-5131.";
        $msgRight .= " </p><p>Thank you for your help.</p>
        <h2> https://dinomitedays.org/</h2>$eol
        <h2>https://dinomitedays.org/kown-locations</h2>$eol$eol
        <h2>locate@dinomitedays.org</h2>$eol
        <h2>call 412-530-5131</h2>$eol$eol
        ";

        foreach ($recs as $rec) {
            $name = $rec["name"];
            $filename = $rec["filename"];
            if (file_exists(self::imageDire . "/$filename.jpg")) {
                $msgRight .= "<img class='knownLoc' src='/" . self::imagePath . "/$filename.jpg' alt='$filename' width='180px' >";
            }
        }

        $msg .= "
        <style>
        .knownLoc {
            margin: 1px;
        }
        </style>
            ";


        $msg .= "<table><tr><td width='45px'>$msgLeft</td><td width='120px'>$msgRight</td></tr></table>";


        return $msg;
    } // end knownLocation

} // end class
