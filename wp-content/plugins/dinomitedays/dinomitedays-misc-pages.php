<?php

ini_set("display_errors", true);
error_reporting(E_ALL);


/* DinomiteDays Misc Pages
 * copyrightMessage()
 * gridView(string $sql, string $displayKey)
 * last_seen(array $attributes)
 * knownLocation(array $attributes)
 * messageRequest()
 * museumsStore(array $attributes)
 *
*/
class dinomitedays_misc_pages
{
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "graphics";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";
    const dinoSelect = "((status ='' and latitude > 0) or (status = 'In Storage' or status = 'Transformed')) and mapDate > '2010-01-01'";



    public static function knownLocation($attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";

        $debugLoc = false;
        $msg .= self::messageRequest();


        $sql = "select keyId,  dinoName, status, filename, logoFileName, mapDate,
                    mapLoc, latitude, longitude, cityState
                    from " .  $wpdbExtra->dinosaurs .
            " where  " . self::dinoSelect . "
             order by mapLoc limit 64";

        if ($debugLoc) $msg .= "$sql $eol";
        $recs = $wpdbExtra->get_resultsA($sql);
        if ($debugLoc) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

        $msgLeft = "<table id='knownLocTable'>\n";
        $msgLeft .= rrwFormat::CellHeaderRow("Last Seen", "Dinosaur Name", "Status", "Location");
        //   $msgLeft .= rrwFormat::CellHeaderSize(15, 40);
        $cnt = 0;
        $sqlKnown = "select keyId,  dinoName, status, filename, logoFileName, mapDate,
                    mapLoc, latitude, longitude, cityState
                    from " .  $wpdbExtra->dinosaurs .
            " where  " . self::dinoSelect . "
             order by dinoName";
        $recs = $wpdbExtra->get_resultsA($sqlKnown);
        if ($debugLoc) $msg .= "$sqlKnown $eol";
        $color = rrwFormat::colorSwap();
        foreach ($recs as $rec) {
            $cnt++;
            if ($cnt > 103) break;
            $mapDate = $rec["mapDate"];
            $filename = $rec["filename"];
            $dinoName = $rec["dinoName"];
            $status = $rec["status"];
            $logoFileName = $rec["logoFileName"];
            if ($mapDate > 2023)
                $mapDate = "Recently";
            $mapDateDisplay = "<a href='/update/?dino=$filename' target='update' >$mapDate</a> \n";
            $mapLoc = $rec["mapLoc"];
            $color = rrwFormat::colorSwap($color);
            $msgLeft .= rrwFormat::CellRow($color, $mapDateDisplay, "<a href='/designs/$filename.htm'>$dinoName</a>", $status, "$mapLoc");
        }
        $msgLeft .= "</table>";  // end table if listed addresses

        //   $msg .= "<table id='leftright'><tr><td>$msgLeft</td><td width=560px >$msgRight</td></tr></table>";
        $msg .= "<div>$msgLeft</div>";
        $knownCount = $wpdbExtra->num_rows;
        $msg .= "<h2 class='pageBreakBefore' >Dinosaurs with Known Information ($knownCount)</h2>$eol";
        $msg .= self::gridDisplay($sqlKnown, "nameOnly");

        //   >Missing Dinosaurs---------------------------------------------------------------------------------;

        //   $msg .= str_repeat("<p>&nbsp; </p>", 12);
        $sql = "select keyId,  dinoName, status, filename, logoFileName, mapDate,
                    mapLoc, latitude, longitude, cityState
                    from " .  $wpdbExtra->dinosaurs .
            " where not (" . self::dinoSelect . ")
                    order by dinoName";
        $wpdbExtra->get_resultsA($sql); // just to get the count
        $unKnownCount = $wpdbExtra->num_rows;
        $msg .= "<h2 class='pageBreakBefore' >Dinosaurs Missing Information ($unKnownCount)</h2>$eol";
        $msg .= self::gridDisplay($sql, "dateLoc");
        $msg .= self::copyrightMessage();
        return $msg;
    }

    public static function listByCity(array $attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $sql = "select keyId,  dinoName, status, filename, logoFileName, mapDate,
                    mapLoc, latitude, longitude, cityState
                    from " .  $wpdbExtra->dinosaurs .
            " where  cityState is not null and cityState != '' AND LATITUDE != 0
             order by cityState, dinoName";
        $msg .= self::gridDisplay($sql, "cityState");
        $msg .= self::copyrightMessage();
        return $msg;
    } // end lastSeen
    public static function feedback(array $attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $msg .= self::messageRequest();
        return $msg;
    } // end feedback

    public static function neighborhood(array $attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoFile);
        $sql = "select mapLoc from " . $wpdbExtra->dinosaurs .
            " where  dinoName = '$dinoFile' or filename = '$dinoFile'";
        $recs = $wpdbExtra->get_resultsA($sql);
        $mapLoc = $recs[0]["mapLoc"];
        $mapLoc = str_replace(" ", "+", $mapLoc);
        $search = "https://www.google.com/search?q=in+which+pittsburgh+neighborhoods+is+$mapLoc+located";
        $buffer = file_get_contents($search);
        $msg .= "Google search for <a href='$search'>search</a> returned: $eol $buffer $eol";
        return $msg;
    } // end neighborhood
    private static function messageRequest()
    {
        global $eol;
        $msgRequest = "<p>I am trying to locate all the Carnegie Museum's 100 or so dinosaurs
        that were placed around the city in 2003.  I have found about 25 of them but most have disappeared.$eol
        This is a list of where I have found those.  $eol
        if you see one someplace else please let me know: You can email me at locate@dinomitedays.org or call 412-530-5131.";
        $msgRequest .= " </p><p>Thank you for your help.</p>
            <h2> https://dinomitedays.org/</h2>$eol
            <h2>https://dinomitedays.org/kown-locations</h2>$eol
            <h2>locate@dinomitedays.org</h2>$eol
            <h2>call 412-530-5131</h2>$eol$eol
            if discussing a particular dinosaur, please include the name of the dinosaur and, if possible, a photo. $eol
        ";
        return $msgRequest;
    } // end messageRequest
    public static function museumsStore(array $attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $msg .= "<p class='entry-title' style='border-bottom: 4px solid #000;' >
                    <a class='external'
                        href='https://stores.carnegiemuseums.org/search.php?search_query=dinosaur/' >
                        >>>>>> Search the Carnegie Museum Store for dinosaur items
                    </a>
                </p>
                ";
        return $msg;
    } // end museumsStore
    private static function gridDisplay(string $sql, string $displayKey)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        try {
            ini_set("display_errors", 1);
            error_reporting(E_ALL);
            $recs = $wpdbExtra->get_resultsA($sql);
            if (empty($recs)) {
                $msg .= "No records found for $sql $eol";
                return $msg;
            }
            $msg .= "<div class='dinomitedaysGrid'> <!-- grid display for $displayKey -->\n";
            foreach ($recs as $rec) {
                $filename = $rec["filename"];
                $dinoName = $rec["dinoName"];
                $dinoNameDisplay1 = str_replace("'", "&quot;", $dinoName);
                $dinoNameDisplay = "<a href='/designs/$filename.htm' >$dinoNameDisplay1</a>";
                $status = $rec["status"];
                $logoFileName = $rec["logoFileName"];
                $logoFileName = str_replace(" ", "%20", $logoFileName);
                $mapLoc = $rec["mapLoc"];
                $dateSeen = $rec["mapDate"];
                $cityState = $rec["cityState"];
                $displayHeight = 130;
                switch ($displayKey) {
                    case "nameOnly":
                        $displayLeft = "";
                        if ("Found" == $status)
                            $displayStatus = "";
                        else
                            $displayStatus = " - $status";
                        $displayBottom = "$dinoNameDisplay $displayStatus";
                        $displayHeight = 180;
                        break;
                    case "dateOnly":
                        $displayLeft = "";
                        $displayBottom = "";
                        $displayStatus = "";
                        $dateYear = substr($dateSeen, 0, 4);
                        $displayBottom = "Last seen:$dateYear";
                        $displayHeight = 180;
                        break;
                    case "dateLoc":
                        $displayLeft = "";
                        $displayBottom = "";
                        $displayStatus = "";
                        $dateYear = substr($dateSeen, 0, 4);
                        $displayBottom = "Last seen:$dateYear $eol $mapLoc$eol $dinoNameDisplay";
                        $displayHeight = 190;
                        break;
                    case "cityState":
                        $displayLeft = "";
                        $displayBottom = "";
                        $displayStatus = "";
                        $dateYear = "";
                        $displayBottom = "<strong>$cityState</strong>$eol $dinoNameDisplay";
                        $displayHeight = 190;
                        break;
                    default:
                        $displayName = "$dinoName";
                } // end switch
                $displayImage = "<img src='/" . self::imagePath . "/$logoFileName' alt='$dinoName' width='120' >";
                $divStyle = "style='float:left; margin: 5px; text-align: center; width: 120px; height: {$displayHeight}px;' ";
                $msg .= "<div class='dinomitedaysGridItem' >$displayImage ";
                if (! empty($displayLeft))
                    $msg .= "$displayLeft";
                if (! empty($displayBottom))
                    $msg .= "$eol$displayBottom";
                $msg .= "</div>\n";
            }
            $msg .= "</div> $eol<!-- grid display for $displayKey -->$eol";
            $msg .= "\n</div><!-- an extra div from somewhere -->\n";
            $msg .= "\n</div><!-- an extra div from somewhere -->\n";
            //           $msg .= "\n</div><!-- an extra div from somewhere -->\n";
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#1339 dinomitedays_misc_pages:gridView: $errorEnd");
        }
        return $msg;
    } // end gridDisplay
    public static function copyrightMessage()
    {
        global $eol;
        $msg = "";
        $msg .= "
<p>Please note: While the copyrights to the dinosaurs continue to be owned by the Museum, the physical dinosaur structures are now privately owned. Thus, at this time, the Museum no longer provides information regarding the locations of the dinosaurs. Please be sure to obtain the Museum's permission before taking and/or using photographs of the dinosaurs for any purposes other than purely private enjoyment and to ask the owner's permission before visiting or taking photographs of the dinosaurs on private property.</p>
";
        return $msg;
    } // end copyrightMessage
} // end class
