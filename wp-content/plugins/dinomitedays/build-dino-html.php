<?php
class BuildDinoHtml
{
    public static function generateHtml($attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL);
            $debugBuild = false;
            if ($debugBuild) $msg .= rrwUtil::print_r($_POST, true, "I#1377 post");
            $cssFile = "https://dinomitedays.org/wp-content/plugins/dinomitedays/dinomitedays.css";
            print "<link rel='stylesheet' id='dinomitedays-css'  href='$cssFile' />";
            $dinoInput = rrwParam::String('dino', $attribute);
            if ($debugBuild)  $msg .= "I#1378 dino Input = '$dinoInput' $eol";
            if (empty($dinoInput)) {
                $msg .= dinomitedays_upload::buildDinoSelectionForm($attribute);
                $msg .= $errorBeg . "Error: E#1382 'dino' parameter is required." . $errorEnd . $eol;
                return $msg;
            }
            $sqlDino = "select * from $wpdbExtra->dinosaurs where dinoName = '$dinoInput' or fileName = '$dinoInput'";
            $dinoData = $wpdbExtra->get_resultsA($sqlDino);
            if (! $dinoData) {
                $msg .= $errorBeg . "Error: E#1392 Dinosaur '$dinoInput' not found." . $errorEnd . "$sqlDino $eol";
                return $msg;
            }
            if (1 != $wpdbExtra->num_rows) {
                $msg .= $errorBeg . "Error: E#1386 Multiple entries found for dinosaur '$dinoInput'." . $errorEnd . $eol;
                return $msg;
            }
            $dinoData = $dinoData[0];
            $fileName = trim($dinoData["fileName"]);
            $submit = rrwParam::String('replace', $attribute);
            if (! empty($submit)) {
                $fileNameCurrent = ABSPATH . "designs/$fileName.htm";
                $fileNameNew = ABSPATH . "designsNew/$fileName.htm";
                $fileNameSave = ABSPATH . "designsOld/$fileName" . "_" . date("Y-m-d_Hi") . ".htm";
                $msg .= "I#1379 rename $fileNameCurrent to $fileNameSave $eol $fileNameNew to $fileNameCurrent $eol";
                rename($fileNameCurrent, $fileNameSave);
                rename($fileNameNew, $fileNameCurrent);
                return $msg;
            }
            if ($debugBuild) $msg .= rrwUtil::print_r($dinoData, true, "I#1381 Dinosaur Data");
            // class for the public page of a dinosaur, which is generated from the database and saved as an HTML file in the designs directory.
            // The file name is the same as the dino name with .htm extension.
            // The page includes the dinosaur name, sponsor, charity, fossil location, auction price, theme, and page content.

            $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dinosaur Information</title>

';
            // Add the CSS styles for this page
            $cssFile = "https://dinomitedays.org/wp-content/plugins/dinomitedays/dinomitedays.css";
            $html .= "<link rel='stylesheet' id='dinomitedays-css'  href='$cssFile' />";
            $html .= '
        </head>
        <body class="body-outside">
        <div class="body-inside">
        ';
            $html .= self::header($dinoData, $msg);
            $html .= self::details($dinoData, $msg);
            $html .= self::body($dinoData, $msg);
            $html .= self::showPictures($dinoData, $msg);
            $html .= self::footer($dinoData, $msg);
            $html .= '</div> <!-- class body-inside -->
            </body>
            </html>';
            $painterName = trim($dinoData["fileName"]);
            $fileNameFull = ABSPATH . "designsNew/$painterName.htm";
            $msg .= "I#1384 Writing file: " . $fileNameFull . $eol;
            $fp = fopen($fileNameFull, 'w');
            fwrite($fp, $html);
            fclose($fp);
            if (strpos($msg, "E#") !== false) {
                $msg .= $errorBeg . "Error: E#1385 Found previous Issues encountered during HTML generation." . $errorEnd . $eol;
                return $msg;    // previous error
            }
            $compare =  '<div class="dino-iframe-left" ><a target="single" href="https://dinomitedays.org/designs/' . $painterName . '.htm" ><h2 >Existing version</h2></a><br />
                                <iframe class="iframe" src="https://dinomitedays.org/designs/' . $painterName . '.htm"  ></iframe></div>
                        <div class="dino-iframe-right"><a target="single" href="https://dinomitedays.org/designsNew/' . $painterName . '.htm" ><h2>New version</h2></a><br />
                                <iframe class="iframe"  src="https://dinomitedays.org/designsNew/' . $painterName . '.htm" ></iframe></div>
               ';
            $compare = "<div class='container'>$compare</div>";
            $msg = $compare . $msg . $eol . $eol;
            $msg .= "$eol$eol
            <table><tr>
                <td><form><input type='submit' name='replace' value='replace the old image' />
                        <input type='hidden' name='dino' value='$fileName' /></form></td>
                <td><form  action='/content' ><input type='submit' name='contents' value='update the about text contents' method='post' >
                        <input type='hidden' name='dino' value='$fileName' /></form> </td>
                <td><form action='/info-update' ><input type='submit' name='information' value='update just the information block' method='post'  >
                        <input type='hidden' name='dino' value='$fileName' /></form> </td>
            </tr></table>
                $eol";
        } catch (Exception $e) {
            $msg .= $errorBeg . "Exception: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // generateHtml
    private static function header($dinoData, &$msg)
    {
        global $eol;
        $html = "<div class='dino-header'><span class='dino-header-text' >" . htmlspecialchars($dinoData["DinoName"]) . " </span>\n
                 <span class='dino-header-image'>
                 <a href='/' > <img src='https://dinomitedays.org/graphics/logo-bordered.png' alt='dinomitedays logo' height=58px ></span></a>
                 &nbsp; &nbsp; </div>";
        return "$html\n";
    }
    private static function details($dinoData, &$msg)
    {
        global $eol;
        // location display consist of three fields: mapLoc, mapDate, and streetViewList.
        // If mapDate is empty, then use status instead of date.
        // If streetViewList is not empty, then add a link to the street view.
        $locationDisplay = $dinoData["mapLoc"];
        if (empty($dinoData["mapDate"]))
            $locationDisplay = $dinoData["Status"];
        else {
            $mapDate = new DateTime($dinoData["mapDate"]);
            $locationDisplay .= " (" . $mapDate->Format("Y-m") . ") ";
        }
        $lat = $dinoData["Latitude"];
        $lng = $dinoData["Longitude"];
        if ($lat != 0 && $lng != 0 && !is_null($lat) && !is_null($lng)) {
            $locationDisplay .= " <a href='https://dinomitedays.org/map/?latitude=$lat&longitude=$lng'>map</a> ";
            $locationDisplay .= " <a href='https://google.com/maps/dir/[$lat,$lng]' >directions</a>";
        }
        if (!empty($streetViewList)) {
            $view = $dinoData["streetViewList"];
            $pano = explode(",", $view);
            $locationDisplay .= "<a href='https://dinomitedays.org/wp-content/plugins/freewheeling-map/pano/panora345.php?&fileName=" . $dinoData["filename"] .
                "&amp;lat=$pano[2]&amp;lng=$pano[3]&amp;zoom=$pano[5]&amp;heading=$pano[0]&amp;pitch=$pano[1]&amp;nohead=1' >
                            <img src='pegman' </a>";
        }
        if (empty($dinoData["ActionPrice"])) {
            $actionDisplay = "";
        } else {
            if (is_numeric($dinoData["ActionPrice"])) {
                $actionValue = floatval($dinoData["ActionPrice"]);
                $actionDisplay = number_format($actionValue, 0, '.', ',');
            } else {
                $actionDisplay = $dinoData["ActionPrice"];
            }
        }
        $html = "<div class='dino-info'>";
        $html .= self::oneLineDetails("Sponsor", $dinoData, "Sponsor", $msg);
        $html .= self::oneLineDetails("Charity", $dinoData, "Charity", $msg);
        $html .= self::oneLineDetails("Fossil Location", $locationDisplay, "", $msg);
        $html .= self::oneLineDetails("Auction Price", $actionDisplay, "", $msg);
        $html .= self::oneLineDetails("Material", $dinoData, "Material", $msg);
        $html .= self::oneLineDetails("Theme", $dinoData, "Theme", $msg);
        $html .= "</div>";
        return "$html\n";
    }
    private static function body($dinoData, &$msg)
    {
        global $eol;
        $html = "<div class='dino-page-content'>\n";
        $html .= "<img class='dino-body-image' src= 'https://dinomitedays.org/designs/images/" . $dinoData["fileName"] . ".jpg'
                    alt='picture of the " . htmlspecialchars($dinoData["DinoName"]) . " dinosaur'  >\n";
        $html .= $dinoData["pageContent"] . $eol;
        $html .= "\n</div>\n"; // close dino-page-content
        return "$html\n";
    }
    private static function oneLineDetails($category, $dinoData, $fieldName, &$msg)
    {
        global $eol;

        if (empty($fieldName))
            $itemContent = $dinoData;
        else
            $itemContent  = $dinoData[$fieldName];
        if (empty($itemContent)) {
            return "";
        }
        $html = "<span class='dino-subheader'>$category: </span> $itemContent $eol";
        return "$html\n";
    }
    private static function showPictures($dinoData, &$msg)
    {
        global $eol;
        $debugPictures = false;
        $html = "";
        $Dire = ABSPATH . "designs/images/";
        $filePreFix = $dinoData["fileName"];
        $numChars = strlen($filePreFix);
        if ($debugPictures) $msg .= "Looking for pictures in directory: $Dire with prefix: '$filePreFix'  with count $numChars characters $eol";
        foreach (new DirectoryIterator($Dire) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $fileName = $fileInfo->getFilename();
            $nn = strncmp($fileName, $filePreFix, $numChars);
            if ($debugPictures) $msg .= "Checking file: $fileName result = $nn $eol";
            if (strpos($fileName, "_sm.jpg") !== false || strpos($fileName, "_th.jpg") !== false)
                continue;
            if (0 == $nn) {
                //$msg .= "I#1391 found file: $fileName $eol";
                $html .= "<img class='dino-bottom-image' src='https://dinomitedays.org/designs/images/$fileName'> \n";
                // alt='picture of the " . htmlspecialchars($dinoData["DinoName"]) . " dinosaur' >\n";
            }
        }   // end foreach file in directory
        return "<div class='dino-pictures'>$html</div>\n";
    }   // end showPictures
    private static function footer($dinoData, &$msg)
    {
        $html = "<div class='dino-footer'>";
        $fileName = ABSPATH . "/wp-content/plugins/dinomitedays/footer_dino.php";
        $fp = fopen($fileName, 'r');
        $html .= fread($fp, filesize($fileName));
        fclose($fp);
        $html .= "</div></div>"; // close container
        return $html;
    }
}// end class BuildDinoHtml
