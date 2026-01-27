<?php

ini_set("display_errors", true);
error_reporting(E_ALL);


/* DinomiteDays Misc Pages
 * last_seen
 * knownLocation
 * messageRequest
 * 
*/
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
            $sql = "select keyid,  dinoName, status, filename, mapDate,
                    maploc, latitude, longitude
                    from " .  self::rrw_dinos;
            if ($numberorder)
                $sql .= " order by keyid";
            else
                $sql .= " order by year(mapDate)  desc, dinoName asc ";
            if ($debugLast) $msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA($sql);
            if ($debugLast) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $yearPast = "not yet";
            foreach ($recs as $rec) {
                $name = $rec["dinoName"];
                $status = $rec["status"];
                $filename = $rec["filename"];
                $mapDate = $rec["mapDate"];
                $maploc = $rec["maploc"];
                $latitude = $rec["latitude"];
                $longitude = $rec["longitude"];
                $keyid = $rec["keyid"];

                $mapYear = new DateTime($mapDate);
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
            $msg .= "
<script>
document.getElementsByClassName ('entry-title').innerHTML = 'last seen - updated 2021-09-01';
</script>
";
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#1338 dinomitedays_misc_pages:last_seen: $errorEnd");
        }
        return $msg;
    } // end last_seen

    public static function knownLocation($attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $msg .= "
<style>
    @media print {
      .pageBreak {
         clear: both;
         page-break-after: always;
    }
}   .knownLoc {
            margin: 1px;
    }
</style>
        ";
        $debugLoc = false;

        $sql = "select keyId,  dinoName, status, filename, logoFileName, mapDate,
                    mapLoc, latitude, longitude
                    from " .  self::rrw_dinos .
            " where status ='' and latitude > 0 and mapDate > '2008-01-01'
             order by mapLoc limit 64";

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
            $filename = $rec["logoFileName"];
            if (empty($fileName))
                $filename = $rec["filename"];
            if ($mapDate > 2023)
                $mapDate = "Recently";
            $mapDateDisplay = "<a href='/upd/?dino=$filename' target='update' >$mapDate</a> \n";
            $mapLoc = $rec["mapLoc"];
            $msgLeft .= rrwFormat::CellRow($mapDateDisplay, $mapLoc);
        }
        $msgLeft .= "</table>";

        $msgRight = self::messageRequest();
        $cnt = 0;
        foreach ($recs as $rec) {
            $name = $rec["dinoName"];
            $filename = $rec["filename"];
            if (file_exists(self::imageDire . "/$filename.jpg")) {
                $msgRight .= "<img class='knownLoc' src='/" . self::imagePath . "/$filename.jpg' alt='$filename' width='120px' >";
                $cnt++;
                if ($cnt > 15) break;
            }
        }

        //   $msg .= "<table><tr><td width='60px'>$msgLeft</td><td width='110px'>$msgRight</td></tr></table>";
        $msg .= "<table><tr><td>$msgLeft</td><td width=560px >$msgRight</td></tr></table>";

        $limit = 6 * 7;
        $sql = "select keyId,  dinoName, status, filename, mapDate,
                    mapLoc, latitude, longitude
                    from " .  self::rrw_dinos .
            "   where mapDate < '2010-01-01'
                    order by mapDate desc limit $limit ";
        if ($debugLoc) $msg .= "$sql $eol";
        $recs2 = $wpdbExtra->get_resultsA($sql);
        if ($debugLoc) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

        //  $msg .=  "<div class='pageBreak' >";
        //  $msg .= self::messageRequest();
        $cntSkip = 0;
        $cntDisplay = 0;
        foreach ($recs2 as $rec) {
            $cntSkip++;
            if ($cntSkip < 1) continue;
            $name = $rec["dinoName"];
            $filename = $rec["filename"];
            if (file_exists(self::imageDire . "/$filename.jpg")) {
                $msg .= "<img class='knownLoc' src='/" . self::imagePath . "/$filename.jpg' alt='$filename' width='150px' >";
                $cntDisplay++;
                if ($cntDisplay > 48) break;
            }
        }
        //   $msg  .= "</div>";

        return $msg;
    } // end knownLocation
    private static function messageRequest()
    {
        global $eol;
        $msgRequest = "I am trying to locate all the Carnegie History Center's 100 or so dinosaurs
        that were placed around the city in 2003.  I have found about 25 of them but most have disappeared.</p><p>
        This is a list of where I have found those.  </p><p>
        if you see one someplace else please let me know: You can email me at locate@dinomitedays.org or call 412-530-5131.";
        $msgRequest .= " </p><p>Thank you for your help.</p>
            <h2> https://dinomitedays.org/</h2>$eol
            <h2>https://dinomitedays.org/kown-locations</h2>$eol$eol
            <h2>locate@dinomitedays.org</h2>$eol
            <h2>call 412-530-5131</h2>$eol$eol
        ";
        return $msgRequest;
    } // end messageRequest
} // end class
