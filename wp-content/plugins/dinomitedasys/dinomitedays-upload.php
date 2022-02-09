<?php

ini_set( "display_errors", true );
error_reporting( E_ALL | E_STRICT );

class dinomitedys_upload {
    const rrw_dino = "wpprrj_00rrwdinos";
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images/";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";

    public static function last_seen( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLast = false;

        try {
            ini_set( "display_errors", true );
            error_reporting( E_ALL | E_STRICT );
            $msg = "";

            $sql = "select name, filename, mapdate, maploc, latitude, longitude from " .
            self::rrw_dino . " order by year(mapDate) ";
            $sql .= " desc ";
            $sql .= ", name asc ";
            if ( $debugLast )$msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA( $sql );
            if ( $debugLast )$msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $yearPast = "not yet";
            foreach ( $recs as $rec ) {
                $name = $rec[ "name" ];
                $filename = $rec[ "filename" ];
                $mapdate = $rec[ "mapdate" ];
                $maploc = $rec[ "maploc" ];
                $latitude = $rec[ "latitude" ];
                $longitude = $rec[ "longitude" ];

                $mapYear = new DateTime( $mapdate );
                $mapYear = $mapYear->format( "Y" );
                if ( $mapYear != $yearPast )
                    $msg .= "<spam style='font-weight:bold; ' > $mapYear </span>$eol";
                $yearPast = $mapYear;
                $msg .= "<a href='/designs/$filename.htm' > $name</a> $maploc
                 <a href='/map/?dino=true&latitude=$latitude&longitude=$longitude' > map</a> $eol";
            }

        } catch ( Exception $ex ) {
            throw new Exception( "$msg $errorBeg E#825 dinomitedys_upload:upload: $errorEnd" );
        }
        return $msg;
    } // end last_seen

    public static function upload( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        global $dropdownList;
        $msg = "";
        try {
            $debug = true;
            $wpdbExtra = new wpdbExtra;
            $dino = rrwUtil::fetchparameterString( "dino" );
            $plugDire = "/wp-content/plugins/dinomitedasys";
            $jsFile = "$plugDire/dropzone.js";
            $cssFile = "$plugDire/dropzone.css";

            if ( !is_array( $dropdownList ) )
                $dropdownList = array();
            $msg .= "
<link rel='stylesheet' id='dropzone-css'  href='$cssFile' />

        ";


            $msg .= "<form method=\"post\" action=\"/upload\" > 
        ";
            $sql = "select * from " . self::rrw_dino . " order by name ";
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
                $source = self::pictureFileUrl( $dino );
            }
            $msg .= "
                <img src='$source' height='150px' />
            </td>
        </tr>
        </table> 
        <br />
            </form>

            <form method=\"post\" action=\"/upload\" enctype=\"multipart/form-data\" > 
        ";

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
                    name='locationDesc'  id='locationDesc'
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
            $msg .= self::dropzone_div( "coordinates" );
            $msg .= "</td><td align='left' valign='center' >Drop file or click to upload</td>
                </tr>
                </table>
            </td> 
        </tr>
       </table> 
       Pictures to be include on the full description page.
       Take pictures from various side of the dinasaur. Avoid shots that show the same image.
       Drop here or click to add.</td> 
    <div class='rrwDinoGrid' >
       ";
            for ( $ii = 0; $ii < 6; $ii++ ) {
                $msg .= self::dropzone_div( 'picture$ii' );
            }
            $msg .= "
        </div>

       <br/>
       <input type=\"submit\" value=\"Click to process this data\" 
                onclick=\"submitClick(this);\" />
    
       <div id=existingPics > ";
            if ( !empty( $dino ) ) {
                $msg .= "
                <table width='400px'><tr>
                <td  width='270px'> <h2> Existing photograph on detail page. </h2><br />
<img src='" . self::detailFileUrl( $dino ) . "' width='270px' />
</td><td align='left'  width='130px'><h2>image used in the picture selection Page</h2><br />
<img src='" . self::pictureFileUrl( $dino ) . "' width='130px' /> <br />
                </td></td>
                </table>
";
            }
            $msg .=
                "</div>           
 
  <script src=\"$jsFile\"></script>
       </form>
<script>
    ";
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
            }
            $msg .= "
</script>
<br />
";


        } catch ( Exception $ex ) {
            throw new Exception( "$msg $errorBeg E#825 dinomitedys_upload:upload: $errorEnd" );
        }
        return $msg;
    } // end upload


    // ------------------------------------------------ create the div
    static private function dropzone_div( $name ) {
        global $dropdownList;
        $msg = "";
        $msg .= "

    <div class=\"drop-zone\" id=\"dropzone_$name\" ondragstart=\"dropzoneDragOver(this);\" ondragsend=\"dropzoneDragLeave_end(this);\" ondragover=\"dropzoneDragOver(this);\" ondragleave=\"dropzoneDragLeave_end(this);\" onchange=\"dropzone_chaange(this, '$name' );\" onclick=\"dropzone_click('$name');\">
        <span class=\"drop-zone__prompt\"></span>
        <input type=\"file\" name=\"$name\" id=\"$name\" class=\"drop-zone__input\">
    </div>
";
        array_push( $dropdownList, $name );
        return $msg;
    }

    static public function process_upload( $atr ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debugSave = true;

        if ( $debugSave ) {
            $msg .= rrwUtil::print_r( $_POST, true, "What was gottem by the submit _post" );
            $msg .= rrwUtil::print_r( $_FILES, true, "the files_files" );
        }
        $basedir = "/home/pillowan/www-dinomitedays"; // get_home_path();
        $images = "$basedir/designs/images";

        $dino = rrwUtil::fetchparameterString( "dino" );

        if ( empty( $dino ) ) {
            return "$msg $errorBeg #807 missing the dinosaur seletion $errorEnd";
        }
        $dino = $_POST[ "dino" ];
        for ( $next_number = 1; $next_number < 20; $next_number++ ) {
            $newfile = "{$dino}next_number.jpg";
            if ( !file_exists( "$images/$newfile" ) ) break;
        }
        $existing_number = $next_number - 1;
        $msg .= "There are $existing_number files already on the 
                <a href='/designs/$dino' >dinosaur's page < /a> $eol";
        $uploads_dir = '/home/pillowan/www-dinomitedays/wp-content/uploads/newpictures';
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

            if ( $error == UPLOAD_ERR_OK ) {
                if ( "coordinates" == $key )
                    $number = "";
                else {
                    $existing_number++;
                    $number = $existing_number;
                }
                $newname = "$uploads_dir/" . $dino . "$number" . "_$key" . "_$filename";
                $answer = move_uploaded_file( $tmp_name, $newname );
                if ( $debugSave )$msg .= "moving $tmp_name to $newname $eol";
                if ( false === $answer ) {
                    $msg .= "$errorBeg E#880 there was a problem in retrieving/move the file $name $errorEnd ";
                } else {
                    $msg .= "I#809 moved file to $newname $eol";
                    if ( "coordinates" != $key ) {
                        $ii = strrpos( $filename, "." );
                        $finalname = "$images/$dino" . ( $existing_number - 1 ) . substr( $filename, $ii );
                        $msg .= "copy( $newname, $finalname )$eol";
                        $answer = copy( $newname, $finalname );
                        if ( false === $answer ) {
                            $msg .= "$errorBeg E#883 there was a problem in copying the fileto $finalname $errorEnd ";
                        } else {
                            $msg .= "I#892 copy file to $newname $eol";
                        }
                    }
                }
            } else {
                $msg .= "$errorBeg error $error uploading the file $filename $errorEnd";
                continue;
            }
        } // end foreash ($files)
        $msg .= $eol;
        return $msg;
    }

    private static function detailFileUrl( $filename ) {
        foreach ( array( ".jpg", "_pic.jpg" ) as $ext ) {
            $fileFull = self::siteDir . self::imagePath . "$filename$ext";
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
} // end class

?>
