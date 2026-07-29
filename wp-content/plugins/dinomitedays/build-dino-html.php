<?php

// This class is responsible for generating and displaying the HTML for a dinosaur, including
class BuildDinoHtml
{
    const httpDesign = "https://dinomitedays.org/designs/";
    const httpDesignNew = self::httpDesign . "New/";
    const httpDesignImages = self::httpDesign . "images/";
    const httpDesignFirstImage = self::httpDesign . "images/";
    const direDesignImages = "/home/pillowan/www-dinomitedays/designs/images/";

    public static function fixUpdateSome($attribute)
    {
        global $wpdbExtra, $eol, $errorBeg, $errorEnd;
        $maxDinosToProcess = 1;
        $msg = "";
        try {
            $sql2Fix = "select * from $wpdbExtra->dinosaurs where not pageContent = ''  order by dinoName limit " . $maxDinosToProcess;
            $recsFix = $wpdbExtra->get_resultsA($sql2Fix);
            $cnt = 0;
            foreach ($recsFix as $dinoData) {
                $cnt++;
                $fileName = trim($dinoData["fileName"]);
                $dinoName = trim($dinoData["dinoName"]);
                $msg .= "I#1350 Processing dinosaur $dinoName -- $fileName" . $eol;
                $msg .= self::generateOneDino($dinoData);    // create the HTML for this dinosaur
                $msg .= self::moveNew2Old($fileName);
            }
        } catch (Exception $e) {
            $msg .= $errorBeg . "Exception: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // end function fixUpdateSome
    private static function displayHtml($attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        try {
            $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoInput);
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
            //           $painterName = trim($dinoData[0]["fileName"]);
            //           $newLink = "https://dinomitedays.org/designsNew/$painterName.htm";
            //           $buffer = file_get_contents($newLink);
            //           return $buffer;
        } catch (Exception $e) {
            $msg .= $errorBeg . "Exception: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // end function displayHtml
    public static function generateHtml(array $attribute)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL);
            $debugBuild = rrwParam::isDebugMode("debugBuild");
            if ($debugBuild)
                $msg .= "I#1351 Starting HTML generation with attributes: " . rrwUtil::print_r($attribute, true) . $eol;
            $show = rrwParam::Boolean("Show", $attribute);
            if (! empty($show)) {
                $msg .= self::displayHtml($attribute);
                return $msg;
            }
            if (rrwUtil::notAllowedToEdit("upload", "dinoFile", true))
                return $msg;
            if ($debugBuild)
                $msg .= rrwUtil::print_r($_POST, true, "I#1352 post");
            $cssFile = "https://dinomitedays.org/wp-content/plugins/dinomitedays/dinomitedays.css";
            print "<link rel='stylesheet' id='dinomitedays-css'  href='$cssFile' />";
            $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoInput);
            $sqlDino = "select * from $wpdbExtra->dinosaurs where dinoName = '$dinoInput' or fileName = '$dinoInput'";
            $dinoData = $wpdbExtra->get_resultsA($sqlDino);
            if (! $dinoData) {
                $msg .= $errorBeg . "Error: E#1393 Dinosaur '$dinoInput' not found." . $errorEnd . "$sqlDino $eol";
                return $msg;
            }
            if (1 != $wpdbExtra->num_rows) {
                $msg .= $errorBeg . "Error: E#1387 Multiple entries found for dinosaur '$dinoInput'." . $errorEnd . $eol;
                return $msg;
            }
            $dinoData = $dinoData[0];
            $msg .= self::generateOneDino($dinoData);    // create the HTML for this dinosaur
            $fileName = trim($dinoData["fileName"]);
            $submit = rrwParam::String('replace', $attribute);
            if (! empty($submit)) { // the save new to final button was clicked.
                $msg .= self::moveNew2Old($fileName);
                return $msg;
            }
            if (strpos($msg, "E#") !== false) {
                $msg .= $errorBeg . "Error: E#1385 Found previous Issues encountered during HTML generation." . $errorEnd . $eol;
                return $msg;    // previous error
            }
            $existingLink = "https://dinomitedays.org/designs/$fileName.htm";
            $newLink = "https://dinomitedays.org/designsNew/$fileName.htm";
            $compare = "<div class='dino-iframe-left' ><a target='single' href='$existingLink' ><h2 >Existing version $existingLink</h2></a><br />
                                <iframe class='iframe' src='$existingLink'  ></iframe></div>
                        <div class='dino-iframe-right'><a target='single' href='$newLink' ><h2>New version $newLink</h2></a><br />
                                <iframe class='iframe'  src='$newLink' ></iframe></div>
               ";
            $compare = "<div class='container'>$compare</div>";
            // output the page content and the compare side by side, and then have buttons to replace the old version with the new one, or to update just the content, or to update just the information block.
            $msg .= $compare . $eol . $eol;
            $msg .= "$eol <form action='/build-dino-page' ><input type='submit' name='replace' value='replace the old version with the new one' />
                        <input type='hidden' name='dino' value='$fileName' /></form>$eol $eol";
        } catch (Exception $e) {
            $msg .= $errorBeg . "Error: E#1394 Exception occurred: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // end of function buildDinoHtml
    public static function moveNew2Old(string $fileName)
    {
        global $eol;
        $msg = "";
        $fileNameCurrent = ABSPATH . "designs/$fileName.htm";
        $urlCurrent = "https://dinomitedays.org/designs/$fileName.htm";
        $fileNameNew = ABSPATH . "designsNew/$fileName.htm";
        $urlNew = "https://dinomitedays.org/designsNew/$fileName.htm";
        $fileNameSave = ABSPATH . "designsOld/$fileName" . "_" . date("Y-m-d_Hi") . ".htm";
        $urlSave = "https://dinomitedays.org/designsOld/$fileName" . "_" . date("Y-m-d_Hi") . ".htm";
        $msg .= "I#1379 rename $fileNameCurrent to <a href='$urlSave' target='save'>$fileNameSave</a> $eol
                        $fileNameNew to <a href='$urlCurrent' target='new'>$fileNameCurrent</a> $eol";
        rename($fileNameCurrent, $fileNameSave);
        rename($fileNameNew, $fileNameCurrent);
        return $msg;
    } // end of function moveNew2Old;
    //
    //  create one dinosaur HTML page from the database data
    //     retrieve from the database,
    //     generate the HTML content for the dinosaur page.
    //     save it as an HTML file in the designsNew directory.
    public static function generateOneDino(array $dinoData)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        try {
            $debugBuild = false;
            if ($debugBuild)
                $msg .= rrwUtil::print_r($dinoData, true, "I#1381 Dinosaur Data");
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
            $html .=
                "
<link rel='stylesheet' id='freewheelingEasyUpload-css-css' href='https://includes.freewheelingeasy.com/dragAnDrop/dd-style.css?ver=6.9.4' media='all' />
<link rel='stylesheet' id='twenty-thirteen-theme-css' href='https://dinomitedays.org/wp-content/themes/twentythirteen/style.css?ver=6.9.4' media='all' />
<link rel='stylesheet' id='twentythirteen-fonts-css' href='https://dinomitedays.org/wp-content/themes/twentythirteen/fonts/source-sans-pro-plus-bitter.css?ver=20230328' media='all' />
<link rel='stylesheet' id='genericons-css' href='https://dinomitedays.org/wp-content/themes/twentythirteen/genericons/genericons.css?ver=20251101' media='all' />
<link rel='stylesheet' id='twentythirteen-style-css' href='https://dinomitedays.org/wp-content/themes/roys-header/style.css?ver=20251202' media='all' />
<link rel='stylesheet' id='twentythirteen-block-style-css' href='https://dinomitedays.org/wp-content/themes/twentythirteen/css/blocks.css?ver=20240520' media='all' />
<link rel='stylesheet' id='dinomitedays-css'  href='$cssFile' />
<!-- Google tag (gtag.js) -->
<script async src='https://www.googletagmanager.com/gtag/js?id=G-QLGRVDXLSZ'></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-QLGRVDXLSZ');
</script>

        </head>

        <body class='body-outside'>
        ";
            $html .= self::header($dinoData);
            $html .= '<div class="body-inside" >
            <div class="body-inside-margin" >';
            $html .= self::details($dinoData, $msg);
            $html .= self::body($dinoData, $msg);
            $html .= self::showPictures($dinoData, $msg);
            $html .= self::footer();
            $html .= '</div> <!-- class body-inside-margin -->
            </div> <!-- class body-inside -->
           </div> <!-- class body-outside -->
            </body>
            </html>';
            $painterName = trim($dinoData["fileName"]);
            $fileNameFull = ABSPATH . "designsNew/$painterName.htm";
            if ($debugBuild)
                $msg .= "I#1384 Writing file: " . $fileNameFull . $eol;
            $fp = fopen($fileNameFull, 'w');
            fwrite($fp, $html);
            fclose($fp);
        } catch (Exception $e) {
            $msg .= $errorBeg . "Exception: " . $e->getMessage() . $errorEnd . $eol;
        }
        return $msg;
    } // generateHtml
    private static function header(array $dinoData)
    {
        global $eol;
        $title = htmlspecialchars($dinoData["dinoName"]);
        $html = "
        <div id=dinoMenu class='menucolor' > <!-- entire space is orange -->
            <table class='menucolor' style='table-layout: auto;' >
            <tr class='menucolor' >
                <td class='site-title menucolor' > $title <br /><br />\n";
        $html .= wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => 'nav-menu menucolor',
            'echo' => false
        ));
        $html .= "
                </td>
                <td class='menucolor' >
                    <a href='/' ><img src='" . site_url("/wp-content/themes/roys-header/images/dinomitedaysLogo-85.png") . "' alt='dinomitedays logo image' > </a>
                </td>
            </tr>
            </table>
        </div>
    ";
        return $html;
    } // end header
    private static function details(array $dinoData, string &$msg)
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
            $locationDisplay .= " (" . $mapDate->Format("Y") . ") ";
        }
        $lat = $dinoData["Latitude"];
        $lng = $dinoData["Longitude"];
        $status = $dinoData["Status"];
        if ($lat != 0 && $lng != 0 && ! is_null($lat) && ! is_null($lng)) {
            $locationDisplay .= " <a href='https://dinomitedays.org/map/?latitude=$lat&longitude=$lng'>map</a> ";
            $locationDisplay .= " <a href='https://google.com/maps/dir/$lat,$lng' >directions</a>";
        } else {
            $locationDisplay .= " $status ";
        }
        if (! empty($streetViewList)) {
            $view = $dinoData["streetViewList"];
            $viewExploded = explode(",", $view);
            $locationDisplay .= "<a href='" . site_url("/wp-content/plugins/freewheeling-map/pano/panora345.php?&fileName=") . $dinoData["filename"] .
                "&amp;lat=$viewExploded[2]&amp;lng=$viewExploded[3]&amp;zoom=$viewExploded[5]&amp;heading=$viewExploded[0]&amp;pitch=$viewExploded[1]&amp;nohead=1' >
                            <img src='pegman' alt='street view pegMan' ></a>";
        }
        if (empty($dinoData["ActionPrice"])) {
            $actionDisplay = "";
        } else {
            if (is_numeric($dinoData["ActionPrice"])) {
                $actionValue = floatval($dinoData["ActionPrice"]);
                $actionDisplay = number_format($actionValue, 0, '.', ',');
                $actionDisplay = "$" . $actionDisplay;
            } else {
                $actionDisplay = $dinoData["ActionPrice"];
            }
        }
        $html = "<div class='dino-info'>";
        $html .= self::oneLineDetails("Sponsor", $dinoData, "Sponsor", $msg);
        $html .= self::oneLineDetails("Charity", $dinoData, "Charity", $msg);
        $html .= self::oneLineDetailString("Fossil Location", $locationDisplay);
        $html .= self::oneLineDetailString("Auction Price", $actionDisplay);
        $html .= self::oneLineDetails("Material", $dinoData, "Material", $msg);
        $html .= self::oneLineDetails("Theme", $dinoData, "Theme", $msg);
        $html .= "</div>";
        return "$html\n";
    }
    /*
This method builds and returns an HTML fragment for the main dinosaur content area.
    $dinoData is an associative array containing the dinosaur's information, including fileName, dinoName, and pageContent.
     $msg return messages during the build process, currently unused but could be used for debugging or error reporting.

    initializes $html with an opening <div> using the class dino-page-content.
     It appends an <img> tag whose src is constructed from a fixed base URL plus $dinoData["fileName"].jpg.
     The alt text includes $dinoData["dinoName"],
    it appends $dinoData["pageContent"] and $eol, then closes the wrapping <div>,
    and returns the complete HTML string.

    */
    private static function body(array $dinoData, string &$msg)
    {
        global $eol;
        $html = "<div class='dino-page-content'>\n";
        $imageUrl = self::httpDesignFirstImage . $dinoData["fileName"] . ".jpg";

        $html .= "<img class='dino-body-image-left' src= '$imageUrl'
                    alt='picture of the " . htmlspecialchars($dinoData["dinoName"]) . " dinosaur'  >\n";
        $html .= $dinoData["pageContent"] . $eol;
        $html .= "\n</div>\n"; // close dino-page-content
        return "$html\n";
    }
    private static function oneLineDetails(string $category, array $dinoData, string $fieldName, string &$msg)
    {
        global $eol;
        if (empty($fieldName))
            $itemContent = $dinoData;
        else
            $itemContent = $dinoData[$fieldName];
        if (empty($itemContent)) {
            return "";
        }
        $html = self::oneLineDetailString($category, $itemContent);
        return $html;
    }
    private static function oneLineDetailString(string $category, string $itemContent)
    {
        global $eol;
        if (empty($itemContent)) {
            return "";
        }
        $html = "<span class='dino-subheader'>$category: </span> $itemContent $eol";
        return "$html\n";
    }
    private static function showPictures(array $dinoData, string &$msg)
    {
        global $eol;
        $debugPictures = false;
        $html = "";
        $Dire = self::direDesignImages;
        $filePreFix = $dinoData["fileName"];
        $numChars = strlen($filePreFix);
        if ($debugPictures)
            $msg .= "Looking for pictures in directory: $Dire with prefix: '$filePreFix'  with count $numChars characters $eol";
        foreach (new DirectoryIterator($Dire) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $fileName = $fileInfo->getFilename();
            $nn = strncmp($fileName, $filePreFix, $numChars);
            if ($debugPictures)
                $msg .= "Checking file: $fileName result = $nn $eol";
            if (strpos($fileName, "_sm.jpg") !== false || strpos($fileName, "_th.jpg") !== false)
                continue;
            if (0 == $nn) {
                //$msg .= "I#1391 found file: $fileName $eol";
                $fileName = str_replace(" ", "%20", $fileName);
                $url = self::httpDesignImages . $fileName;
                $html .= "<img class='dino-bottom-image' src='$url'
                                alt='picture of the " . htmlspecialchars($dinoData["dinoName"]) . " dinosaur - $fileName'> \n";
                // alt='picture of the " . htmlspecialchars($dinoData["dinoName"]) . " dinosaur' >\n";
            }
        }   // end foreach file in directory
        return "<div class='dino-pictures'>$html</div>\n";
    }   // end showPictures

    public static function footer()
    {
        $html = '
<div class="dino-footer">
	<div class="menu-menu-1-container">';
        $html .= wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => 'nav-menu menucolor',
            'echo' => false
        ));
        $html .= "<span class='dino-footer-copyright'>Copyright <a href='https://carnegiemnh.org/'>Carnegie Museum of Natural History</a> &nbsp;
					Hosted by the book <em><a href='https://freewheelingeasy.com/'>FreewheelingEasy in Western Pennsylvania</a></em> &nbsp;
					<a href='" . site_url('/feedback/')  . "'>Contact Us</a>
					</span>
	</div> <!-- close menu-menu-1-container -->
</div> <!-- close dino-footer -->"; // close container
        return $html;
    } // end function footer

}// end class BuildDinoHtml