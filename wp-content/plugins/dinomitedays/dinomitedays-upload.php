<?php

ini_set("display_errors", false);
error_reporting(E_ALL | E_STRICT);

$picDire = "/home/pillowan/www-shaw-weil-pictures/wp-content/plugins";
require_once "$picDire/roys-picture-processng/uploadProcessDire.php";
/*  class uploadProcessDire {
 *       nameToBottom( $sourceFile, $photographer ) 
 *       resizeImage( $pathin, $pathout, $w_max, $h_max ) {
 *   }
 */

class dinomitedys_upload
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
        global $dropdownList; // used to create the scriptfile with this input
        global $wpdbExtra, $rrw_dinos;
        $msg = "";
        try {
            if (!is_user_logged_in()) {
                $msg .= freewheeling_edit_setGlobals::showLoginform(
                    "to update the information. $eol
                    Login below or to get a login, Contact <a href='https://mail.google.com/mail/u/0/?fs=1&tf=cm&to=dinodaysWebmaster@shaw-weil.com'> the webmaster</a> with who you are, email, and why you should
                        have access.<br />"
                );
                return $msg;
            }
            $msg .= $_SERVER['REQUEST_URI'];
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
            $msg .= self::buildDinoSelectionForm($dino, "update");
            if ($debugProgress) $msg .= "after first form, okay $eol";
            if (empty($dino))
                return $msg;
            // dinoe selected so display more information
            $msg .= self::displayExisting($dino, true);
            // was submit clicked ?
            if (empty($submit)) {
                $msg .= self::displayPhotosForm($dino); // no ! 
            } else {
                $msg .= self::processInputPhotos(); // yes !
            }
            $msg .= "dino is now $dino $eol";
            $msg .= self::formForPictures($dino, $jsFile);
            $msg .= "<br />
 <hr width='2px'><h2> Existing photographs on page 
 <a href='/designs/$dino.htm' target='pic'> $dino.htm</a> </h2>$eol";
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#766 main update $errorEnd";
        }
        return $msg;
    } // end upload

    private static function updatHTMfile($dino)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";

        $SqlMaploc = "select Maploc, latitude, longitude
                            from $rrw_dinos where name = '$dino' ";
        $recs = $wpdbExtra->get_resultsA($SqlMaploc);
        $maploc = $recs[0]["maploc"];
        $latitude = $recs[0]["latitude"];
        $longitude = $recs[0]["longitude"];
        $sqlupdat = array();
        $sqlupdat["maploc"] = $maploc;
        $sqlupdat["latitude"] = $latitude;
        $sqlupdat["longitude"] = $longitude;
        $sqlWhere = array("name" => $dino);
        $result = $wpdbExtra->Update($rrw_dinos, $sqlupdat, $sqlWhere);
        $filenameFull = "$dino" . "_.htm";
        $msg .= dinomitedys_make_html::updateFosilLocations(
            $filenameFull,
            $maploc,
            $latitude,
            $longitude
        );


        return $msg;
    }

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

        $msg .= "<form method=\"post\" action=\"/update\" enctype=\"multipart/form-data\" > 
            <input type='hidden' name='dino' id='dino' value='$dino' />
        ";
        $sqldino = "select * from $rrw_dinos where filename = '$dino' ";
        $recDinos = $wpdbExtra->get_resultsA($sqldino);
        if (1 != $wpdbExtra->num_rows)
            throw new Exception("$msg $errorBeg E#79 did not find the
                            dinosauer $errorEnd $sqldino $eol");
        $recDino = $recDinos[0];
        // $msg .= rrwUtil::print_r($recDino, true, "recDino");
        $mapLoc = $recDino["Maploc"];
        $mapdate = $recDino["Mapdate"];
        $latitude = $recDino["Latitude"];
        $longitude = $recDino["Longitude"];
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
        <input type='text' value='$mapdate' name='mapdate' id='mapdate' />
        <br>
                </td>
                
            <td class=\"freewheel_td\" >
                <strong>Location Cordinates:</strong> can be determined from a  photgraph taken 
                    with a device that has location turned on. Should be taken very close to the dinosauer. 
                    Will not be uased in the collection of photographs on  the detail page.
               <table>
               <tr>
                  <td width=\"60 px\" >
               ";
        if ($debugProgress) $msg .= "About to first fropzone $eol";
        $msg .= self::dropzone_div("coordinates");
        if ($debugProgress) $msg .= "after first dropzone $eol";
        $msg .= "</td>
                <td align='left' valign='center' >
                    Drop file with embeded location data or enter values $eol
                    Latitude  <input name='latitude' id='latitude' type='text' value='$latitude' > $eol
                    Longitude <input name='longitude' id='longitude' type='text' value='$longitude' > $eol
                </td>
                </tr>
                </table>
            </td> 
        </tr>
       </table> ";

        return $msg;
    } //end displayPhotosForm

    private static function formForPictures($dino, $jsFile = "")
    {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList;
        $msg = "";

        try {
            $debugProgress = false;
            $filelist = dinomitedys_make_html::findRelated($dino, true);
            $fileSort = 10; // at least 10
            foreach ($filelist as $key => $value) {
                $matches = array();
                $parse = preg_match("/^[\D]*([0-9]*)[\D]*$/", $key, $matches);
                if (1 == $parse) {
                    if ($debugProgress) $msg .= "formForPictures:
                        max( $fileSort, " . $matches[1] . ")$eol";
                    $fileSort = max($fileSort, $matches[1]);
                }
            }
            $msg .= "<div class='rrwDinoGrid' > ";
            for ($ii = 0; $ii < 6; $ii++) {
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
            throw new Exception("$msg E#7947 " . $ex->getMessage() .
                "$errorBeg dinomitedys_:formForPictures $errorEnd");
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
            $filelist = dinomitedys_make_html::findRelated($dino, $labels);
            if ($debugProgress) $msg .= rrwUtil::print_r($filelist, true, "found files");
            $msg .= "<div id='dinoImages' class='rrwDinoGrid'>\n";
            foreach ($filelist as $pic => $dummy) {
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
            throw new Exception(" $msg E#795 " . $ex->getMessage() .
                "$errorBeg dinomitedys_:displayExisting $errorEnd");
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
                $msg .= rrwUtil::print_r($_POST, true, "What was gottem by the submit _post");
                $msg .= rrwUtil::print_r($_FILES, true, "the files_files");
            }
            $images = self::imageDire;

            $dino = rrwPara::String("dino");
            $fileSort = rrwPara::String("filesort");
            $photographer = rrwPara::String("photographer");
            if ($fileSort < 10)
                $fileSort = 10;
            if ($debugSave) $msg .= "dino = $dino, filesort = $fileSort, 
                                        photographer = $photographer $eol";

            if (empty($dino)) {
                return "$msg $errorBeg W#797 missing the dinosaur seletion $errorEnd";
            }

            if ($debugSave) $msg .= "$fileSort is the highest sort number 
            already on the   <a href='/designs/$dino.htm' target='production'
            > dinosaur $dino's  page </a> $eol";
            $uploads_dir = self::siteDir . self::imageSavePath;
            $keySelect = array("filename" => $dino);
            //
            // extract the location description and enter into dataase
            $locationDesc = rrwPara::String("locationDesc");
            $sqlup = array("maploc" => $locationDesc);

            // latitude, longitude may be overwritten by the the image/file named coordinates
            $latitude = rrwPara::String("latitude");
            $sqlup["latitude"] = $latitude;
            $longitude = rrwPara::String("longitude");
            $sqlup["longitude"] = $longitude;
            //
            // extract the mapdate and enter into dataase
            $mapdate = rrwPara::String("mapdate");
            $sqlup["mapdate"] = $mapdate;
            if ($debugSave) {
                $msg .= rrwUtil::print_r($sqlup, true, "sql update");
                $msg .= rrwUtil::print_r($keySelect, true, "sql select");
            } else {
                $msg .= rrwUtil::print_r($sqlup, true, "sql update");
            }
            $wpdbExtra->update($rrw_dinos, $sqlup, $keySelect);
            $numrowsUpdated = $wpdbExtra->num_rows; // did we change anything
            if ($numrowsUpdated > 0) {  // we changed the database, update original file
                $msg .= "attempting to update the original htm file $eol";
                $msg .= dinomitedys_make_html::updateFosilLocations($dino);
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
                    // extract the coordianates and enter into database
                    $exif = exif_read_data($tmp_name);
                    if (array_key_exists("latitude", $exif)) {
                        $lat = $exif["latitude"];
                        $lng = $exif["longitude"];
                    } else {
                        $lat = 0;
                        $lng = 0;
                    }
                    if (0 == $lat || false === $lat || 0 == $lng || false === $lng) {
                        $msg .= "$errorBeg E#750 Got invalid coordinates of '$lat, $lng' from the location file. No update occured.";
                    } else {
                        // check ranges
                        $sqlup = array("latitude" => $lat, "longitude" => $lng);
                        $cnt = $wpdbExtra->update($rrw_dinos, $sqlup, $keySelect);
                        if (1 == $cnt) $msg .= "i#754 Coordinates updated. Please check 
                            <a href='/last_seen/' > last seen </a> and the map $eol";
                        else
                            $msg .= "$errorBeg E#752 Something went wrong in the database update. $errorEnd ";
                        $msg .= rrwUtil::print_r($sqlup, true, "the update array");
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
                    $msg .= "$errorBeg E#759 there was a problem in retrieving/move the file '$tmp_name' to '$saveName' $errorEnd ";
                    continue;
                }
                $numberOfSavedImages++;
                if ($debugSave) $msg .= "----------------------------- $eol
                                        I# moved file to  $saveName $eol";
                $finalName = self::imageDire . $shortName;
                if ($debugSave) $msg .= "E#764 resizeImage( 
                        $saveName, $finalName, 700, 200 ) $eol";
                $msg .= uploadProcessDire::resizeImage($saveName, $finalName, 700, 200);
                if (!empty($photographer)) {
                    if ($debugSave) $msg .= "I#799 nameToBottom( 
                                    $finalName, $photographer ); $eol";

                    $msg .= uploadProcessDire::nameToBottom($finalName, $photographer);

                    if ($debugSave)
                        $msg .= "I#760 $saveName resized, attributed to $finalName $eol";
                } // end if (!empty($photographer))
            } // end foreash ($files)
            $msg .= $eol;
            if ($numberOfSavedImages > 0) {
                $msg .= "I#789 $$numberOfSavedImages files uploaded $eol";
                $msg .= dinomitedys_make_html::UpdateImages($dino);
            }
        } // end try
        catch (Exception $ex) {
            $msg .= $ex->getMessage() . "$errorBeg  E#780 update $errorEnd";
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
