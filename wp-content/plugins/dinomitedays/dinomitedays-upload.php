<?php

ini_set( "display_errors", true );
error_reporting( E_ALL | E_STRICT );

class dinomitedys_upload {
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";


    public static function upload( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList; // used to create the scriptfile with this input
        global $wpdbExtra, $rrw_dinos;
        $msg = "";
        try {
            $debug = false;
            $debugProgress = false;
            $wpdbExtra = new wpdbExtra;
            $rrw_dinos = "wpprrj_00rrwdinos";
            $cssFile = self::http . "wp-content/plugins/dinomitedays/dropzone.css";
            $msg .= "<link rel='stylesheet' id='dropzone-css'  href='$cssFile' />";

            $dino = rrwUtil::fetchparameterString( "dino" );
            $submit = rrwUtil::fetchparameterString( "submit" );
            if ( $debugProgress ) {
                $msg .= "dino = $dino, submit = $submit $eol ";
                $msg .= rrwUtil::print_r( $_POST, true, "post data " );
            }
            $plugDire = "/wp-content/plugins/dinomitedays";
            $jsFile = "$plugDire/dropzone.js";

            if ( !is_array( $dropdownList ) )
                $dropdownList = array();

            $msg .= self::DisplayDinoSelection( $dino );
            if ( $debugProgress )$msg .= "after first form, okay $eol";
            if ( empty( $dino ) )
                return $msg;

            if ( empty( $submit ) ) {
                $msg .= self::displayPhotosForm( $dino );
            } else {
                $msg .= self::processInputPhotos( $dino );
            }
            $msg .= "dino is now $dino $eol";
            $msg .= self::formForPictures( $dino, $jsFile );
            $msg .= "<br />
 <hr width='2px'><h2> Existing photographs on page 
 <a href='/designs/$dino.htm' target='pic'> $dino.htm</a> </h2>$eol";
            $msg .= self::displayExisting( $dino, true );
        } // end try
        catch ( Exception $ex ) {
            $msg .= $ex->getMessage() . "$errorBeg  E#430 main upload $errorEnd";
        }
        return $msg;
    } // end upload

    private static function DisplayDinoSelection( $dino ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";

        // display a dino selection form
        $msg .= "<form method=\"post\" action=\"/upload\" > ";
        $sql = "select * from $rrw_dinos order by name ";
        $recs = $wpdbExtra->get_resultsA( $sql );
        //      $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol "; 
        $msg .= "<table style=\"border-collapse: collapse; \">
            <tr class=\"freewheel_td\" >
            <td style=\"vertical-align:middle; \">
        ";
        $msg .= '<font color=red >Required</font><br />
            <select id="dino" name="dino" oninput="submit();" >';
        if ( empty( $dino ) )
            $msg .= '<option value="" disabled selected >Pick a dinosaur. </option>
        ';
        foreach ( $recs as $rec ) {
            $name = $rec[ "Name" ];
            $file = $rec[ "Filename" ];
            $msg .= '<option value="' . $file . '"';
            if ( $dino == $name )
                $msg .= " selected=$dino ";
            $msg .= "> $name </option>\n";
        }
        $msg .= "</select> 
            </td>
            <td>";
        if ( empty( $dino ) )
            $source = "/graphics/white.gif";
        else {
            $source = self::imagePath . "/$dino" . "_sm.jpg";
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


    private static function displayPhotosForm( $dino ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";

        $debugProgress = false;
        $msg .= "<form method=\"post\" action=\"/upload\" enctype=\"multipart/form-data\" > 
            <input type='hidden' name='dino' value='$dino' />
        ";
        $sqldino = "select * from $rrw_dinos where filename = '$dino' ";
        $recDinos = $wpdbExtra->get_resultsA( $sqldino );
        if ( 1 != $wpdbExtra->num_rows )
            throw new Exception( "$msg $errorBeg E#664 did not find the
                            dinosauer $errorEnd $sqldino $eol" );
        $recDino = $recDinos[ 0 ];
        $mapLoc = $recDino[ "Maploc" ];
        $limit = 140;
        $size = 50;
        $msg .= "
        <table>
        <tr>
            <td class=\"freewheel_td\" >
           
                <strong>Location Description:</strong> This should help a user to locate the dinosaur. 
                <br> &nbsp; &nbsp;Such as a street address or
                <br> &nbsp; &nbsp;building name with guide to where inside.<br \>
                <font color=pink >optional</font><br>
                <input type='text' maxlength='$limit' size='$size' 
                    name='locationDesc'  id='locationDesc' value='$mapLoc'
                   onkeyup='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onkeydown='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onmouseout='countChars(\"locationDesc\",\"locationLeft\", $limit);' />
                <br> &nbsp; &nbsp; &nbsp; &nbsp; 
                <span id=\"locationLeft\">$limit</span> Characters left </td>
                
            <td class=\"freewheel_td\" >
                <strong>Location Cordinates:</strong> can be determined from a  photgraph taken 
                    with a device that has location turned on. Should be taken very close to the dinosauer. 
                    Will not be uased in the collection of photographs on  the detail page.
               <table><tr><td width=\"60 px\" >
               ";
        if ( $debugProgress )$msg .= "About to first fropzone $eol";
        $msg .= self::dropzone_div( "coordinates" );
        if ( $debugProgress )$msg .= "after first dropzone $eol";
        $msg .= "</td><td align='left' valign='center' >Drop file or click to upload</td>
                </tr>
                </table>
            </td> 
        </tr>
       </table> ";

        return $msg;
    } //end displayPhotosForm

    private static function formForPictures( $dino, $jsFile = "" ) {
        global $eol, $errorBeg, $errorEnd;
        global $dropdownList;
        $msg = "";
        try {
            $debugProgress = false;
            $filelist = dinomitedys_make_html_class::findRelated( $dino, true );
            $fileCount = count( $filelist );
            $msg .= "<div class='rrwDinoGrid' > ";
            for ( $ii = 0; $ii < 6; $ii++ ) {
                $msg .= self::dropzone_div( "picture$ii" );
            }
            if ( $debugProgress )$msg .= "after input drop zones $eol";
            $msg .= " </div>
                    <br/>
                    <input type='hidden' name='filecount' value='$fileCount' />
                    <input type=\"submit\" value=\"Click to process this data\" 
                            name=\"submit\" onclick=\"submitClick(this);\" />";
            $dire = self::siteDir . self::imagePath;
            if ( $debugProgress )$msg .= "find related $eol";
            if ( $debugProgress )$msg .= "after find related $eol";
            $msg .= "
  <script src=\"$jsFile\">
  </script>
  </form>\n<script>\n";
            foreach ( $dropdownList as $name ) {
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
            getElementById('dino').focus;
            </script> $eol";
        } catch ( Exception $ex ) {
            throw new Exception( "E#825 $msg E#825 " . $ex->getMessage() .
                "$errorBeg E#825 dinomitedys_:formForPictures $errorEnd" );
        }
        return $msg;
    } // end formForPictures

    public static function displayExisting( $dino, $labels ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        try {
            $debugProgress = false;
            // --------------------------------------  existing photos
            if ( $debugProgress )$msg .= "about to display existing photos$$eol";
            if ( $debugProgress )$msg .= rrwUtil::print_r( $filelist, true,
                "found files" );
            $cntImage = 0;
            // -----------------------------  display the collection
            $filelist = dinomitedys_make_html_class::findRelated( $dino, $labels );
            $msg .= "<div id='dinoImages' class='rrwDinoGrid'>\n";
            foreach ( $filelist as $pic => $dummy ) {
                $cntImage++;
                $msg .= "<div class='rrwDinoItem' >" .
                        "<img src='/" . self::imagePath . "/$pic' width='270px' />";
                if ( $labels ) {
                    $filesize = self::imageDire . "/$pic";
                    if ( file_exists( $filesize ) ) {
                        $size = getimagesize( $filesize );
                        $meta = $size[ 0 ] . " X " . $size[ 1 ];
                    } else {
                        $meta = "";
                    }
                    $msg .= "<br />$pic $meta";
                    if ( $cntImage > 3 )
                        $msg .= "<a href='/fixit/?task=rejectdesginimage&amp;file=$pic' > reject</a>";
                }
                $msg .= "</div>";
            } // for each impage to display
            $msg .= "</div> <!-- end dinoImages -->\n"; /* match the rrwDinoGrid  */

        } catch ( Exception $ex ) {
            throw new Exception( "E#825 $msg E#825 " . $ex->getMessage() .
                "$errorBeg E#825 dinomitedys_:displayExisting $errorEnd" );
        }
        return $msg;
    } // end displayExisting


    // ------------------------------------------------ create a dropzone div
    static private function dropzone_div( $name ) {
        global $dropdownList; // used to create the scriptfile with this input
        $msg = "";
        $msg .= "

    <div class=\"drop-zone\" id=\"dropzone_$name\" ondragstart=\"dropzoneDragOver(this);\" ondragsend=\"dropzoneDragLeave_end(this);\" ondragover=\"dropzoneDragOver(this);\" ondragleave=\"dropzoneDragLeave_end(this);\" onchange=\"dropzone_chaange(this, '$name' );\" onclick=\"dropzone_click('$name');\">
        <span class=\"drop-zone__prompt\"></span>
        <input type=\"file\" name=\"$name\" id=\"$name\" class=\"drop-zone__input\">
    </div>
";
        array_push( $dropdownList, $name );
        return $msg;
    } // end dropzone_div

    static public function processInputPhotos( $atr ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos;
        $msg = "";
        $debugSave = false;

        if ( $debugSave ) {
            $msg .= rrwUtil::print_r( $_POST, true, "What was gottem by the submit _post" );
            $msg .= rrwUtil::print_r( $_FILES, true, "the files_files" );
        }
        $basedir = "/home/pillowan/www-dinomitedays"; // get_home_path();
        $images = "$basedir/designs/images";

        $dino = rrwUtil::fetchparameterString( "dino" );
        $fileCount = rrwUtil::fetchparameterString( "filecount" );

        if ( empty( $dino ) ) {
            return "$msg $errorBeg #807 missing the dinosaur seletion $errorEnd";
        }

        $msg .= "There are $fileCount files already on the 
                <a href='/designs/$dino.htm' target='production' >dinosaur $dino's  page </a> $eol";
        $uploads_dir = '/home/pillowan/www-dinomitedays/designs/images';
        foreach ( $_FILES as $key => $fileInfo ) {
            if ( $debugSave ) {
                $msg .= "------------------------------- $eol ";
                $msg .= rrwUtil::print_r( $key, true, "the key" );
                $msg .= rrwUtil::print_r( $fileInfo, true, "error" );
            }
            $error = $fileInfo[ "error" ];
            $filename = $fileInfo[ "name" ];
            $size = $fileInfo[ "size" ];
            $tmp_name = $fileInfo[ "tmp_name" ];
            if ( ( 4 == $error ) && empty( $filename ) && ( 0 == $size ) )
                continue; // no entry is this dropbox

            if ( $error != UPLOAD_ERR_OK ) {
                $msg .= uploadErrorMsg( $err );
                continue;
            }
            if ( "coordinates" == $key ) {
                // extract the coordianates and enter into database
                $exif = exif_read_data( $tmp_name );
                $lat = $exif[ "latitude" ];
                $lng = $exif[ "longitude" ];
                if ( 0 == $lat || false === $lat || 0 == $lng || false === $lng ) {
                    $msg .= "$errorBeg E#755 Got invalid coordinates of '$lat, $lng' from the location file. No update occured.";
                }
                // check ranges
                $sql = "update $rrw_dinos set latitude ='$lat', longitude = '$lng'
                            where 'filename' = '$dino' ";
                $cnt = $wpdbExtra->query( $sql );
                if ( 1 == $cnt )$msg .= "i#754 Coordinates updated. Please check 
                    <a href='/last_seen/' > last seen </a> and the map $eol";
                else
                    $msg .= "$errorBeg E#752 Something went wrong in the database update. $errorEnd $sql $eol";
                continue; // next file
            }
            $fileCount++;
            $newname = "$uploads_dir/" . $dino . "_$fileCount" . "_$filename";
            $answer = move_uploaded_file( $tmp_name, $newname );
            if ( $debugSave )$msg .= "moving $tmp_name to $newname $eol";
            if ( false === $answer ) {
                $msg .= "$errorBeg E#880 there was a problem in retrieving/move the file $name $errorEnd ";
                continue;
            }
            $msg .= "I#809 moved file to $newname $eol";
        } // end foreash ($files)
        $msg .= $eol;
        $msg .= dinomitedys_make_html_class::UpdateImages( $dino );
        return $msg;
    } // end process_upload

    private static function uploadErrorMsg( $err ) {
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
        if ( $err > 8 || $err < 0 )
            return "Unkown file upload error #$err ";
        return $phpFileUploadErrors[ $err ];
    }


    /*
        private static function detailFileUrl( $filename ) {
            foreach ( array( ".jpg", "_pic.jpg" ) as $ext ) {
                $fileFull = . "$filename$ext";
                if ( file_exists( $fileFull ) )
                    return self::http . self::imagePath . "$filename$ext";
            }
            return $fileURL;
        }
        private static function pictureFileUrl( $filename ) {
            $fileFull = self::http . self::imagePath . $filename . "_sm.jpg";
            return $fileFull;
        }
        private static function listofFileFull( $filename ) {
            $igonore1 = detailFilefull( $filename );
            $igonore2 = pictureFileFull( $filename );
            $list = array();

            $hd = opendir( $self::imagedir );
            while ( false !== ( $entry = readdir( $hd ) ) ) {
                if ( 0 != strncmp( $entry, $filename, strlen( $filename ) ) )
                    continue;
                if ( $ignore1 == $entry || $ignore2 == $entry )
                    continue;
                array_push( $list, $self::http . self::imagePath . "$entry" );
            }
            return $list;
        }
        private static function fileURL( $fileFull ) {
            $url = str_replace( self::imagedire, "design/images/", $fileFull );
            $url = get_site_url() . "$url";
            return $url;

        }
        */
} // end class

?>
