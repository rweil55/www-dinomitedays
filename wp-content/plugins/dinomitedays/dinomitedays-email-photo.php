<?php

ini_set("display_errors", false);
error_reporting(E_ALL);

$picDire = "/home/pillowan/www-shaw-weil-pictures/wp-content/plugins";
require_once "$picDire/roys-picture-processing/uploadProcessDire.php";
/*  class uploadProcessDire {
 *       nameToBottom( $sourceFile, $photographer )
 *       resizeImage( $pathin, $pathout, $w_max, $h_max ) {
 *   }
 */

class dinomitedays_email_photo
{
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imageSavePath = "wp-content/new-images";
    const imagePath = "designs/images/";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";
    const dinoPlugin = self::http . "wp-content/plugins/dinomitedays/";

    public static function uploadEmail($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList; // used to create the scriptfile with this input
        global $wpdbExtra, $rrw_dinos;
        ini_set("display_errors", true);
        error_reporting(E_ALL);

        $msg = "";
        try {
            //   $msg .= $_SERVER['REQUEST_URI'];
            $debug = false;
            $debugProgress = false;
            $cssFile = self::dinoPlugin . "dinomitedays.css";
            $msg .= "<link rel='stylesheet' id='dropzone-css'  href='$cssFile' />";

            $dino = rrwUtil::fetchparameterString("dino");
            $submit = rrwUtil::fetchparameterString("submit");
            if ($debugProgress) {
                $msg .= "dino = $dino, submit = $submit $eol ";
                $msg .= rrwUtil::print_r($_POST, true, "post data ");
            }
            $plugDire = "/wp-content/plugins/dinomitedays";
            $jsFile = "$plugDire/dropzone.js";

            if (!is_array($dropdownList))
                $dropdownList = array();
            // build the input form
            $msg .= self::buildDinoSelectionForm($dino, "uploadEmail");
            if ($debugProgress) $msg .= "after first form, okay $eol";
            if (!empty($dino))
                // dino selected so display more information
                $msg .= self::displayExisting($dino, true);
            // was submit clicked ?
            if (empty($submit)) {
                $msg .= self::displayPhotosForm($dino); // no !
            } else {
                $msg .= self::processInputPhotos(); // yes !
            }
            $msg .= "dino is now $dino $eol";
            $msg .= self::formForPictures($dino, $jsFile);
            $msg .= $eol;
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#1336 main update $errorEnd";
        }
        return $msg;
    } // end upload

    private static function updatHTMfile($dino)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";

        $SqlMapLoc = "select mapLoc, latitude, longitude
                            from $rrw_dinos where name = '$dino' ";
        $recs = $wpdbExtra->get_resultsA($SqlMapLoc);
        $mapLoc = $recs[0]["mapLoc"];
        $latitude = $recs[0]["latitude"];
        $longitude = $recs[0]["longitude"];
        $sqlUpadaeHTM = array();
        $sqlUpadaeHTM["mapLoc"] = $mapLoc;
        $sqlUpadaeHTM["latitude"] = $latitude;
        $sqlUpadaeHTM["longitude"] = $longitude;
        $sqlWhere = array("name" => $dino);
        $result = $wpdbExtra->Update($rrw_dinos, $sqlUpadaeHTM, $sqlWhere);
        $filenameFull = "$dino" . "_.htm";
        $msg .= dinomitedays_make_html::updateFossilLocations(
            $filenameFull,
            $mapLoc,
            $latitude,
            $longitude
        );


        return $msg;
    }

    /**
     * Builds a form to select a dinosaur.
     *
     * @param string $dino The previously selected dinosaur.
     * @param string $action The action URL where the form will be submitted.
     * @return string The HTML form as a string.
     */
    private static function buildDinoSelectionForm($dino, $action)
    {
        //  build a <form to make a dino section
        //  $dino    a previous selected dino
        //  $action  where to go wher it is selected
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";

        $msg .= "<form method=\"post\" action=\"/$action\" > ";

        $sql = "select * from $rrw_dinos order by name ";
        $recs = $wpdbExtra->get_resultsA($sql);
        //      $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";
        $msg .= "<table style=\"border-collapse: collapse; \">
            <tr class=\"freewheel_td\" >
            <td style=\"vertical-align:middle; \">
        ";
        $msg .= '<font color=red >Required</font><br />
            <select id="dino" name="dino" oninput="submit();" >';
        if (empty($dino))
            $msg .= '<option value="" disabled selected >Pick a dinosaur. </option>
        ';
        foreach ($recs as $rec) {
            $name = $rec["Name"];
            $file = $rec["Filename"];
            $msg .= '<option value="' . $file . '"';
            if ($dino == $file)
                $msg .= " selected=$dino ";
            $msg .= "> $name </option>\n";
        }
        $msg .= "</select>
            </td>
            <td>";
        if (empty($dino))
            $source = "/graphics/white.gif";
        else {
            $source = self::imagePath . "$dino" . "_sm.jpg";
        }
        $msg .= "
                <img src='$source' height='150px' />
            </td>
        </tr>
        </table>
        <br />
            </form>";
        return $msg;
    }


    private static function displayPhotosForm($dino)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos, $rrw_photographers;
        $msg = "";

        $debugProgress = false;
        $photographer = rrwUtil::fetchparameterString("photographer");

        $msg .= "<form method=\"post\" action=\"/uploadEmail\" enctype=\"multipart/form-data\" >
            <input type='hidden' name='dino' id='dino' value='$dino' />
        ";
        $msg .= "<input type='hidden' name='dino' id='dino' value='$dino' />\n";
        $limit = 140;
        $size = 50;
        $today = date("Y-m-d");
        $msg .= "
        <table>
        <tr>
            <td class=\"freewheel_td\" >

                <strong>Location Description:</strong> This should help a user to locate the dinosaur.
                <br> &nbsp; &nbsp;Such as a street address or
               building name with guide to where inside.<br \>
                <input type='text' maxlength='$limit' size='$size'
                    name='locationDesc'  id='locationDesc' value=''
                   onkeyup='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onkeydown='countChars(\"locationDes<h3></h3>c\",\"locationLeft\", $limit);'
                   onmouseout='countChars(\"locationDesc\",\"locationLeft\", $limit);' />
                <br> &nbsp; &nbsp; &nbsp; &nbsp;
                <span id=\"locationLeft\">$limit</span> Characters left
                $eol $eol <strong>Photographer</strong> <font color=red >Required if photos below</font>$eol
                  <input type='text' value='$photographer' name='photographer' id='photographer' />
        $eol $eol <strong>Last Seen</strong>
        <input type='text' name='mapdate' id='mapdate' value='$today' />
        <br>
                </td>

        </tr>
        </table>
       ";

        return $msg;
    } //end displayPhotosForm

    private static function formForPictures($dino, $jsFile = "")
    {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList;
        $msg = "";

        try {
            $debugProgress = false;
            $filelist = dinomitedays_make_html::findRelated($dino, true);
            $fileSort = 10; // at least 10
            foreach ($filelist as $key => $value) {
                $matches = array();
                $parse = preg_match("/^[\D]*([0-9]*)[\D]*$/", $key, $matches);
                if (1 == $parse) {
                    if ($debugProgress) $msg .= "formForPictures:
                        max( $fileSort, " . $matches[1] . ")$eol";
                    $fileSort = max($fileSort, $matches[1]);
                    $fileSort++; // next one up
                }
            }
            $msg .= "<div class='rrwDinoGrid' > ";
            for ($ii = 0; $ii < 5; $ii++) {
                $msg .= self::dropzone_div("picture$ii");
            }
            if ($debugProgress) $msg .= "after input drop zones $eol";
            $msg .= " </div>
                    <br/>
                    <input type='hidden' name='filesort' value='$fileSort' />
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
            throw new Exception("$msg E#13647 " . $ex->getMessage() .
                "$errorBeg dinomitedays_:formForPictures $errorEnd");
        }
        return $msg;
    } // end formForPictures

    public static function displayExisting($dino, $labels)
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
            $msg .= "<div id='dinoImages' class='rrwDinoGrid'>\n";
            foreach ($fileList as $pic => $dummy) {
                $cntImage++;
                $img = "/" . self::imagePath . "$pic";
                $msg .= "<div class='rrwDinoItem' >
                    <a href='$img' ><img src='$img' width='270px' /></a>";
                if ($labels) {
                    $filesize = self::imageDire . "/$pic";
                    if (file_exists($filesize)) {
                        $size = getimagesize($filesize);
                        $meta = $size[0] . " X " . $size[1];
                    } else {
                        $meta = "";
                    }
                    $msg .= "<br />$pic $meta";
                    if ($cntImage > 3)
                        $msg .= "<br/><a href='/fixit/?task=rejectdesginimage&amp;file=$pic' > reject</a>";
                }
                $msg .= "\n</div>";
            } // for each impage to display
            $msg .= "</div> <!-- end dinoImages -->\n"; /* match the rrwDinoGrid  */
        } catch (Exception $ex) {
            throw new Exception(" $msg E#1365 " . $ex->getMessage() .
                "$errorBeg dinomitedays_:displayExisting $errorEnd");
        }
        return $msg;
    } // end displayExisting


    // ------------------------------------------------ create a dropzone div
    static private function dropzone_div($name)
    {
        global $dropdownList; // used to create the scriptfile with this input
        $msg = "";
        $msg .= "

    <div class=\"drop-zone\" id=\"dropzone_$name\" ondragstart=\"dropzoneDragOver(this);\" ondragsend=\"dropzoneDragLeave_end(this);\" ondragover=\"dropzoneDragOver(this);\" ondragleave=\"dropzoneDragLeave_end(this);\" onchange=\"dropzone_chaange(this, '$name' );\" onclick=\"dropzone_click('$name');\">
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
        //      save to wp-content/newpictures
        //      determine count and include it in name
        //      resize, add photographer
        //      create "new" dinosaurer display
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";
        $debugSave = false;

        try {
            if ($debugSave) {
                $msg .= rrwUtil::print_r($_POST, true, "What was gotten by the submit _post");
                $msg .= rrwUtil::print_r($_FILES, true, "the files_files");
            }
            $images = self::imageDire;

            $dino = rrwParam::String("dino");
            $fileSort = rrwParam::String("filesort");
            $photographer = rrwParam::String("photographer");
            if ($fileSort < 10)
                $fileSort = 10;
            if ($debugSave) $msg .= "dino = $dino, filesort = $fileSort,
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

            // extract the note and enter into database
            $sqlUpdateArray["note"] = rrwParam::String("note");
            //
            // extract the mapdate and enter into dataase
            $mapdate = rrwParam::String("mapdate");
            $sqlUpdateArray["mapdate"] = $mapdate;
            //
            if ($debugSave) {
                $msg .= rrwUtil::print_r($sqlUpdateArray, true, "sql update");
                $msg .= rrwUtil::print_r($keySelect, true, "sql select");
            } else {
                $msg .= rrwUtil::print_r($sqlUpdateArray, true, "sql update");
            }

            //
            $numberOfSavedImages = 0;
            $fileNamesMoved = "";

            foreach ($_FILES as $key => $fileInfo) {
                if ($debugSave) {
                    $msg .= "------------------------------- $eol ";
                    $msg .= rrwUtil::print_r($key, true, "the key");
                    $msg .= rrwUtil::print_r($fileInfo, true, "error");
                }
                $error = $fileInfo["error"];
                $fileName = $fileInfo["name"];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $size = $fileInfo["size"];
                $tmp_name = $fileInfo["tmp_name"];
                if ((4 == $error) && empty($fileName) && (0 == $size))
                    continue; // no entry is this dropbox
                if ("jpg" != $extension  && "png" != $extension && "jpeg" != $extension) {
                    $msg .= rrwUtil::print_r($fileInfo, true, "the file info");
                    $msgError = "$errorBeg E#1375 The file '$fileName' is not a jpg/jpeg/png file. It is $fileName. $errorEnd ";
                    throw new Exception("$msg $msgError");
                }

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
                        $msg .= "$errorBeg E#1370 Got invalid coordinates of '$lat, $lng' from the location file. No update occured.";
                    } else {
                        // check ranges
                        $sqlUpdateArray = array("latitude" => $lat, "longitude" => $lng);
                        $cnt = $wpdbExtra->update($rrw_dinos, $sqlUpdateArray, $keySelect);
                        if (1 == $cnt) $msg .= "i#1374 Coordinates updated. Please check
                            <a href='/last_seen/' > last seen </a> and the map $eol";
                        else
                            $msg .= "$errorBeg E#1372 Something went wrong in the database update. $errorEnd ";
                        $msg .= rrwUtil::print_r($sqlUpdateArray, true, "the update array");
                    }
                    continue; // on to next file
                } // end if (coordinates
                //
                $imageData = implode("||", $sqlUpdateArray);

                $fileSort++;
                $shortName = $dino . "_$fileSort" . "_$fileName" . "_$imageData";
                $saveName = "$uploads_dir/$shortName";
                if ($debugSave) $msg .= "moving $tmp_name to $saveName $eol";
                $answer = move_uploaded_file($tmp_name, $saveName);
                if (false === $answer) {
                    $msg .= "$errorBeg E#1379 there was a problem in retrieving/move the file '$tmp_name' to '$saveName' $errorEnd ";
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
                else {
                    $msg .= "$errorBeg W#1383 No photographer so no attribution $errorEnd ";
                }
                $fileNamesMoved .= "$saveName, ";
            } // end foreach ($files)
            $msg .= $eol;
            if ($numberOfSavedImages > 0) {
                $msg .= "I#1359 $$numberOfSavedImages files uploaded with names of: $fileNamesMoved $eol";
            }
            $to = "dinoAdmin@royweil.com";
            $subject = "New dinosaur(s) uploaded to dinomitedays.org";
            $body = "The following $numberOfSavedImages files were uploaded to dinomitedays.org by $photographer. \n
                The files are: $fileNamesMoved \n
                Please check them and move to \n /home/pillowan/www-dinomitedays/www-dinomitedays/designs/images. \n
                Thanks, \n
                Roy Weil \n ";
            $headers[] = "FROM: dinoPhoto@royweil.com ";
            $mailResult = wp_mail($to, $subject, $body, $headers);
            if ($mailResult)
                $msg .= "I#1387 An email was sent to the administrator. $eol";
            else
                $msg .= "$errorBeg E#1389 There was a problem sending the email to the admin. $errorEnd
            please copy any error messages from above and use the feedback form to explain what you did and the errors you got. $eol
            $body $eol $headers[0] $eol ";
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#1350 update $errorEnd";
            throw new Exception("$msg");
        }
        return $msg;
    } // end process_upload

    private static function uploadErrorMsg($err)
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
            return "Unkown file upload error #$err ";
        return $phpFileUploadErrors[$err];
    }
} // end class
