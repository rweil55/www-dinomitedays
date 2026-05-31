<?php

ini_set("display_errors", false);
error_reporting(E_ALL);

$picDire = "/home/pillowan/www-shaw-weil-pictures/wp-content/plugins";
require_once "$picDire/roys-picture-processing/uploadProcessDire.php";
/*  class uploadProcessDire {
 *       nameToBottom( $sourceFile, $photographer )
 *       resizeImage( $pathIn, $pathOut, $w_max, $h_max ) {
 *   }
 */

class dinomitedays_upload
{
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imageSavePath = "wp-content/new-images";
    const imagePath = "designs/images/";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";
    const dinoPlugin = self::http . "wp-content/plugins/dinomitedays/";

    public static function upload($attr)
    {

        global $eol, $errorBeg, $errorEnd;
        global $dropdownList; // used to create the script file with this input
        $dropdownList = array(); // used to create the script file with this input
        $msg = "<!-- upload page #2 --------------------------------------------------------------------------- -->\n";

        try {
            if (rrwUtil::notAllowedToEdit("upload", "dinoFile", true))
                return $msg;
            $debugProgress = rrwParam::isDebugMode("progress");
            $cssFile = self::dinoPlugin . "dinomitedays.css";
            $msg .= "<link rel='stylesheet' id='dropzone-css'  href='$cssFile' />";
            $plugDire = "/wp-content/plugins/dinomitedays";
            $jsFile = "$plugDire/dropzone.js";

            $dinoFile = "";
            $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoFile);
            if (empty($dinoFile)) {
                return $msg;
            }
            //  assert we now have the dinosaur file name in $dinoFile
            $submit = rrwUtil::fetchparameterString("submit");
            if (!empty($submit)) {
                $msg .= self::processInputPhotos(); // yes !
            }

            if ($debugProgress) $msg .= "after first form, okay $eol";
            // dino selected so display more information
            $msg .= self::displayExisting($dinoFile, true);
            // was submit clicked ?
            if (empty($submit)) {
                $msg .= self::displayPhotosForm($dinoFile); // no !
            } else {
                $msg .= self::processInputPhotos(); // yes !
            }
            $msg .= "dino is now $dinoFile $eol";
            $msg .= self::formForPictures($dinoFile, $jsFile);
            $msg .= "<br />
 <hr width='2px'><h2> Existing photographs on page
 <a href='/designs/$dinoFile.htm' target='pic'> $dinoFile.htm</a> </h2>$eol";
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#1336 main update $errorEnd";
        }
        return $msg;
    } // end upload

    private static function updateHTMfile(string $dino)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";

        $SqlMapLoc = "select mapLoc, latitude, longitude
                            from $wpdbExtra->dinosaurs where dinoName = '$dino' ";
        $recs = $wpdbExtra->get_resultsA($SqlMapLoc);
        $mapLoc = $recs[0]["mapLoc"];
        $latitude = $recs[0]["latitude"];
        $longitude = $recs[0]["longitude"];
        $sqlUpdateHTM = array();
        $sqlUpdateHTM["mapLoc"] = $mapLoc;
        $sqlUpdateHTM["latitude"] = $latitude;
        $sqlUpdateHTM["longitude"] = $longitude;
        $sqlWhere = array("dinoName" => $dino);
        $result = $wpdbExtra->Update($wpdbExtra->dinosaurs, $sqlUpdateHTM, $sqlWhere);
        $filenameFull = "$dino" . "_.htm";
        $msg .= dinomitedays_make_html::updateFossilLocations($filenameFull);


        return $msg;
    }

    /**
     * Builds a form to select a dinosaur.
     * * from sets the $_POST "dino" which is the filename of the dinosaur record in the database. This is used to populate the form with existing data and to know which dinosaur to update when the form is submitted.
     *
     * @param string $dinoFile The previously selected dinosaur.
     * @return string The HTML form as a string.
     */
    public static function buildDinoSelectionForm(&$dinoFile): string
    {
        //  build a <form to make a dino section
        //  $dino    a previous selected dino
        //  $action  where to go where it is selected
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $optionName = "dinomitedays_dino";
        $dinoFile1 = get_option($optionName);
        $dinoFile2 = rrwParam::String("dino", $_POST);
        if (empty($dinoFile2)) {
            $dinoFile = $dinoFile1;
        } else {
            $dinoFile = $dinoFile2;
            update_option($optionName, $dinoFile2, true);
        }
        $target = "target='edit'";
        $msg .= "
        <h2>Update
            [ <a href='/hunters' $target >Hunters</a> ]
            [ <a href='/tracker' $target >Tracker </a> ]
            [ <a href='/leads' $target >hints about where to look </a> ]
             [ <a href='/upload' $target >Upload</a> ]";
        if (is_user_logged_in()) {
            $msg .= "
            [ <a href='/info-update' target='infoUpdate' >Information Block</a> ]
            [ <a href='/update/' $target >Photos</a> ]
            [ <a href='/content/' $target>Page Content</a> ]
            [ <a href='/build-dino-page/' $target >Compare Page Content</a> ]";
        }
        if (! empty($dinoFile)) {
            $msg .= " [ <a href='/designs/$dinoFile.htm' target='finalPage' >Show Page</a> ]";
        }
        $msg .= "</h2>";

        $msg .= "<form method=\"post\" > ";

        $sql = "select dinoName, filename, logoFilename from $wpdbExtra->dinosaurs order by dinoName ";
        $recs = $wpdbExtra->get_resultsA($sql);
        //      $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";
        $msg .= "<table style=\"border-collapse: collapse; \">
            <tr class=\"freewheel_td\" >
            <td style=\"vertical-align:middle; \">
        ";
        $msg .= '<font color=red >Required</font><br />
            <select id="dino" name="dino" oninput="submit();" >';
        if (empty($dinoFile))
            $msg .= '<option value="" disabled selected >Pick a dinosaur. </option>
        ';
        $SavedLogoFilename = "white.gif";
        foreach ($recs as $rec) {
            $name = $rec["dinoName"];
            $file = $rec["filename"];
            $logoFilename = $rec["logoFilename"];
            $msg .= '<option value="' . $file . '"';
            if ($dinoFile == $file || $dinoFile == $name) {
                $msg .= " selected ";
                $SavedLogoFilename = $logoFilename;
            }
            $msg .= "> $name  </option>\n";
        } // end foreach
        $sql2 = str_replace("by dinoName", "by filename", $sql);
        $recs = $wpdbExtra->get_resultsA($sql2);
        foreach ($recs as $rec) {
            $name = $rec["dinoName"];
            $file = $rec["filename"];
            $logoFilename = $rec["logoFilename"];
            $msg .= "<option value=\"$file\">$file</option>\n";
        } // end foreach
        $msg .= "</select>
            </td>
            <td>";
        $source = site_url() . "/graphics/$SavedLogoFilename"; // default at the end of the selection
        $msg .= "
                <img height='75' src='$source'  />
            </td>
        </tr>
        </table>
        <br />
            </form>";
        return $msg;
    } // end buildDinoSelectionForm


    private static function displayPhotosForm(string $dino)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_photographers;
        $msg = "";

        $debugProgress = false;
        $photographer = rrwUtil::fetchparameterString("photographer");

        $msg .= "<form method=\"post\" action=\"/update\" enctype=\"multipart/form-data\" >
            <input type='hidden' name='dino' id='dino' value='$dino' />
        ";
        $sqlDino = "select * from $wpdbExtra->dinosaurs where filename = '$dino' ";
        $records = $wpdbExtra->get_resultsA($sqlDino);
        if (1 != $wpdbExtra->num_rows)
            throw new Exception("$msg $errorBeg E#1337 did not find the
                            dinosaur $errorEnd $sqlDino $eol");
        $recDino = $records[0];
        // $msg .= rrwUtil::print_r($recDino, true, "recDino");
        $mapLoc = $recDino["mapLoc"];
        $mapDate = $recDino["mapDate"];
        $latitude = $recDino["Latitude"];
        $longitude = $recDino["Longitude"];
        $sponsor = $recDino["Sponsor"];
        $status = $recDino["Status"];
        $notes = $recDino["notes"];
        $msg .= "<input type='hidden' name='dino' id='dino' value='$dino' />\n";
        $limit = 140;
        $size = 50;
        $msg .= "
        <table>
        <tr>
            <td class=\"freewheel_td\" >

                <strong>Location Description:</strong> This should help a user to locate the dinosaur.
                <br> &nbsp; &nbsp;Such as a street address or
                <br> &nbsp; &nbsp;building name with guide to where inside.<br \>
                <input type='text' maxlength='$limit' size='$size'
                    name='locationDesc'  id='locationDesc' value='$mapLoc'
                   onkeyup='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onkeydown='countChars(\"locationDes<h3></h3>c\",\"locationLeft\", $limit);'
                   onmouseout='countChars(\"locationDesc\",\"locationLeft\", $limit);' />
                <br> &nbsp; &nbsp; &nbsp; &nbsp;
                <span id=\"locationLeft\">$limit</span> Characters left
                $eol $eol <strong>Photographer</strong> <font color=red >Required if photos below</font>$eol
            <select id=\"photographer\" name=\"photographer\" >
                <option value=\"\"  >Pick a photographer. </option>
            ";
        $sqlPhotog = "select * from $rrw_photographers ";
        $recs = $wpdbExtra->get_resultsA($sqlPhotog);
        foreach ($recs as $rec) {
            $name = $rec["photographer"];
            $msg .= '<option value="' . $name . '"';
            if ($photographer == $name)
                $msg .= " selected ";
            $msg .= "> $name </option>\n";
        }
        $msg .= "</select>
        $eol $eol <strong>Last Seen</strong>
        <input type='text' value='$mapDate' name='mapDate' id='mapDate' />
        <br>
                </td>

            <td class=\"freewheel_td\" >
                <strong>Location Coordinates:</strong> can be determined from a  photograph taken
                    with a device that has location turned on. Should be taken very close to the dinosaur.
                    Will not be used in the collection of photographs on  the detail page.
               <table>
               <tr>
                  <td width=\"60 px\" >
               ";
        if ($debugProgress) $msg .= "About to first dropzone $eol";
        $msg .= self::dropzone_div("coordinates");
        if ($debugProgress) $msg .= "after first dropzone $eol";
        $msg .= "</td>
                <td align='left' valign='center' >
                    Drop file with embedded location data or enter values $eol
                    Latitude  <input name='latitude' id='latitude' type='text' value='$latitude' > $eol
                    Longitude <input name='longitude' id='longitude' type='text' value='$longitude' > $eol";
        $msg .=  "Status:" . self::statusField("status", $status);
        $msg .= "$eol Notes: <textarea name='notes' id='notes' width='auto' > $notes</textarea>$eol $eol
                    sponsored by <a href='https://www.google.com/search?q=$sponsor' target='sponsor' > $sponsor </a> $eol
                    <a href='https://www.latlong.net/convert-address-to-lat-long.html' target='latLong' > Convert address to lat long </a> $eol
                </td>
                </tr>
                </table>
            </td>
        </tr>
       </table> ";

        return $msg;
    } //end displayPhotosForm

    private static function statusField(string $field, string $currentValue)
    {
        global $wpdbExtra;
        $fieldNew = "<select id=\"$field\" name=\"$field\"  >";
        $sqlStatus = "select distinct $field from $wpdbExtra->dinosaurs order by $field";
        $recs = $wpdbExtra->get_resultsA($sqlStatus);
        foreach ($recs as $rec) {
            $statusItem = $rec["status"];
            $fieldNew .= '<option value="' . $statusItem . '"';
            if ($currentValue == $statusItem)
                $fieldNew .= " selected ";
            $fieldNew .= "> $statusItem </option>\n";
        }
        $fieldNew .= "</select>";
        return $fieldNew;
    }

    private static function formForPictures(string $dino, string $jsFile = "")
    {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList;
        $msg = "";

        try {
            $debugProgress = false;
            $fileList = dinomitedays_make_html::findRelated($dino, true);
            $fileSort = 10; // at least 10
            foreach ($fileList as $key => $value) {
                $matches = array();
                $parse = preg_match("/^[\D]*([0-9]*)[\D]*$/", $key, $matches);
                if (1 == $parse) {
                    if ($debugProgress) $msg .= "formForPictures:
                        max( $fileSort, " . $matches[1] . ")$eol";
                    $fileSort = max($fileSort, $matches[1]);
                }
            }
            $msg .= "<div class='dinomitedaysGrid' > ";
            for ($ii = 0; $ii < 6; $ii++) {
                $msg .= self::dropzone_div("picture$ii");
            }
            if ($debugProgress) $msg .= "after input drop zones $eol";
            $msg .= " </div>
                    <br/>
                    <input type='hidden' name='fileSort' value='$fileSort' />
                    <input type=\"submit\" value=\"Click to process this data\"
                            name=\"submit\" onclick=\"submitClick(this);\" />";
            if ($debugProgress) $msg .= "find related $eol";
            if ($debugProgress) $msg .= "after find related $eol";
            $msg .= "
  <script src=\"$jsFile\">
  </script>
  </form>\n<script>\n";
            foreach ($dropdownList as $name) {
                $msg .= "
        console.log( 'dropping $name' );
        dropRegion = document.getElementById( 'dropzone_$name' );
        dropRegion.addEventListener( 'dragenter', function(ev){ ev.preventDefault()} );
        dropRegion.addEventListener( 'dragleave', function(ev){ ev.preventDefault()} );
        dropRegion.addEventListener( 'dragover',  function(ev){ ev.preventDefault()} );
        dropRegion.addEventListener( 'drop',  function(ev){ ev.preventDefault()} );
        dropRegion.addEventListener( 'drop', dropzone_drop, false );
        ";
            } // end foreach
            $msg .= "
            document.getElementById('dino').focus;
            </script> $eol";
        } catch (Exception $ex) {
            throw new Exception("$msg E#1375 " . $ex->getMessage() .
                "$errorBeg dinomitedays_:formForPictures $errorEnd");
        }
        return $msg;
    } // end formForPictures

    public static function displayExisting(string $dino, bool $labels)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        try {
            $debugProgress = false;
            // --------------------------------------  existing photos
            if ($debugProgress) $msg .= "about to display existing photos $dino $eol";
            $cntImage = 0;
            // -----------------------------  display the collection
            $fileList = dinomitedays_make_html::findRelated($dino, $labels);
            if ($debugProgress) $msg .= rrwUtil::print_r($fileList, true, "found files");
            $msg .= "<div id='dinoImages' class='dinomitedaysGrid'>\n";
            foreach ($fileList as $pic => $dummy) {
                $cntImage++;
                $img = "/" . self::imagePath . "$pic";
                $msg .= "<div class='dinomitedaysGridItem' >
                    <a href='$img' ><img src='$img' width='270px' /></a>";
                if ($labels) {
                    $fileName = self::imageDire . "/$pic";
                    if (file_exists($fileName)) {
                        $size = getimagesize($fileName);
                        $meta = $size[0] . " X " . $size[1];
                    } else {
                        $meta = "";
                    }
                    $msg .= "<br />$pic $meta";
                    if ($cntImage > 3)
                        $msg .= "<br/><a href='/fixit/?task=rejectdesignimage&amp;file=$pic' > reject</a>";
                }
                $msg .= "\n</div>";
            } // for each page to display
            $msg .= "</div> <!-- end dinoImages -->\n"; /* match the rrwDinoGrid  */
        } catch (Exception $ex) {
            throw new Exception(" $msg E#1365 " . $ex->getMessage() .
                "$errorBeg dinomitedays_:displayExisting $errorEnd");
        }
        return $msg;
    } // end displayExisting


    // ------------------------------------------------ create a dropzone div
    static private function dropzone_div(string $name)
    {
        global $dropdownList; // used to create the script file with this input
        $msg = "";
        $msg .= "

    <div class=\"drop-zone\" id=\"dropzone_$name\" ondragstart=\"dropzoneDragOver(this);\" ondragsend=\"dropzoneDragLeave_end(this);\" ondragover=\"dropzoneDragOver(this);\" ondragleave=\"dropzoneDragLeave_end(this);\" onchange=\"dropzone_change(this, '$name' );\" onclick=\"dropzone_click('$name');\">
        <span class=\"drop-zone__prompt\"></span>
        <input type=\"file\" name=\"$name\" id=\"$name\" class=\"drop-zone__input\">
    </div>
";
        array_push($dropdownList, $name);
        return $msg;
    } // end dropzone_div

    static public function processInputPhotos()
    {
        // get file
        // if location, extract coordinates and update database else
        //      save to wp-content/newPictures
        //      determine count and include it in name
        //      resize, add photographer
        //      create "new" dinosaur display
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugSave = false;

        try {
            if ($debugSave) {
                $msg .= rrwUtil::print_r($_POST, true, "What was got by the submit _post");
                $msg .= rrwUtil::print_r($_FILES, true, "the files_files");
            }
            $images = self::imageDire;

            $dino = rrwParam::String("dino");
            $fileSort = rrwParam::String("fileSort");
            $photographer = rrwParam::String("photographer");
            if ($fileSort < 10)
                $fileSort = 10;
            if ($debugSave) $msg .= "dino = $dino, fileSort = $fileSort,
                                        photographer = $photographer $eol";

            if (empty($dino)) {
                return "$msg $errorBeg W#1367 missing the dinosaur selection $errorEnd";
            }

            if ($debugSave) $msg .= "$fileSort is the highest sort number
            already on the   <a href='/designs/$dino.htm' target='production'
            > dinosaur $dino's  page </a> $eol";
            $uploads_dir = self::siteDir . self::imageSavePath;
            $keySelect = array("filename" => $dino);
            //
            // extract the location description and enter into database
            $locationDesc = rrwParam::String("locationDesc");
            $sqlUpdateArray = array("mapLoc" => $locationDesc);

            // latitude, longitude may be overwritten by the the image/file named coordinates
            $latitude = rrwParam::String("latitude");
            $iiComma = strpos($latitude, ",");
            if (false !== $iiComma) {
                $longitude = substr($latitude, $iiComma + 1);
                $latitude = substr($latitude, 0, $iiComma - 1);
                $sqlUpdateArray["longitude"] = $longitude;
                $sqlUpdateArray["latitude"] = $latitude;
            } else {
                $sqlUpdateArray["latitude"] = $latitude;
                $longitude = rrwParam::String("longitude");
                $sqlUpdateArray["longitude"] = $longitude;
            }
            // extract the status and enter into database
            $sqlUpdateArray["status"] = rrwParam::String("status");

            // extract the note and enter into database
            $sqlUpdateArray["notes"] = rrwParam::String("notes");
            //
            // extract the mapDate and enter into database
            $mapDate = rrwParam::String("mapDate");
            $sqlUpdateArray["mapDate"] = $mapDate;
            //
            if ($debugSave) {
                $msg .= rrwUtil::print_r($sqlUpdateArray, true, "sql update");
                $msg .= rrwUtil::print_r($keySelect, true, "sql select");
            } else {
                $msg .= rrwUtil::print_r($sqlUpdateArray, true, "sql update");
            }
            $wpdbExtra->update($wpdbExtra->dinosaurs, $sqlUpdateArray, $keySelect);
            $numrowsUpdated = $wpdbExtra->num_rows; // did we change anything
            if ($numrowsUpdated > 0) {  // we changed the database, update original file
                $msg .= "attempting to update the original htm file $eol";
                $msg .= dinomitedays_make_html::updateFossilLocations($dino);
                $msg .= file_get_contents("https://edit.shaw-weil.com/make-dino-map-files/?nohead=1");
            }
            //
            $numberOfSavedImages = 0;
            foreach ($_FILES as $key => $fileInfo) {
                if ($debugSave) {
                    $msg .= "------------------------------- $eol ";
                    $msg .= rrwUtil::print_r($key, true, "the key");
                    $msg .= rrwUtil::print_r($fileInfo, true, "error");
                }
                $error = $fileInfo["error"];
                $filename = $fileInfo["name"];
                $size = $fileInfo["size"];
                $tmp_name = $fileInfo["tmp_name"];
                if ((4 == $error) && empty($filename) && (0 == $size))
                    continue; // no entry is this dropbox

                if ($error != UPLOAD_ERR_OK) {
                    $msg .= self::uploadErrorMsg($error);
                    continue;
                }
                if ("coordinates" == $key) {
                    // extract the coordinates and enter into database
                    $exif = exif_read_data($tmp_name);
                    if (array_key_exists("latitude", $exif)) {
                        $lat = $exif["latitude"];
                        $lng = $exif["longitude"];
                    } else {
                        $lat = 0;
                        $lng = 0;
                    }
                    if (0 == $lat || false === $lat || 0 == $lng || false === $lng) {
                        $msg .= "$errorBeg E#1370 Got invalid coordinates of '$lat, $lng' from the location file. No update occurred.";
                    } else {
                        // check ranges
                        $sqlUpdateArray = array("latitude" => $lat, "longitude" => $lng);
                        $cnt = $wpdbExtra->update($wpdbExtra->dinosaurs, $sqlUpdateArray, $keySelect);
                        if (1 == $cnt) $msg .= "i#1374 Coordinates updated. Please check
                            <a href='/last_seen/' > last seen </a> and the map $eol";
                        else
                            $msg .= "$errorBeg E#1372 Something went wrong in the database update. $errorEnd ";
                        $msg .= rrwUtil::print_r($sqlUpdateArray, true, "the update array");
                    }
                    continue; // on to next file
                } // end if (coordinates
                //

                $fileSort++;
                $shortName = $dino . "_$fileSort" . "_$filename";
                $saveName = "$uploads_dir/$shortName";
                if ($debugSave) $msg .= "moving $tmp_name to $saveName $eol";
                $answer = move_uploaded_file($tmp_name, $saveName);
                if (false === $answer) {
                    $msg .= "$errorBeg E#1380 there was a problem in retrieving/move the file '$tmp_name' to '$saveName' $errorEnd ";
                    continue;
                }
                $numberOfSavedImages++;
                if ($debugSave) $msg .= "----------------------------- $eol
                                        I# moved file to  $saveName $eol";
                $finalName = self::imageDire . $shortName;
                if ($debugSave) $msg .= "E#1334 resizeImage(
                        $saveName, $finalName, 700, 200 ) $eol";
                //  $msg .= uploadProcessDire::resizeImage($saveName, $finalName, 700, 200);
                if (!empty($photographer)) {
                    if ($debugSave) $msg .= "I#1369 nameToBottom(
                                    $finalName, $photographer ); $eol";

                    //      $msg .= uploadProcessDire::nameToBottom($finalName, $photographer);

                    if ($debugSave)
                        $msg .= "I#1330 $saveName resized, attributed to $finalName $eol";
                } // end if (!empty($photographer))
            } // end foreach ($files)
            $msg .= $eol;
            if ($numberOfSavedImages > 0) {
                $msg .= "I#1359 $$numberOfSavedImages files uploaded $eol";
                $msg .= dinomitedays_make_html::UpdateImages($dino);
            }
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#1350 update $errorEnd";
            throw new Exception("$msg");
        }
        return $msg;
    } // end process_upload

    private static function uploadErrorMsg(string $err)
    {
        $phpFileUploadErrors = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        );
        if ($err > 8 || $err < 0)
            return "Unknown file upload error #$err ";
        return $phpFileUploadErrors[$err];
    }
} // end class
