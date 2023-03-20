<?php

ini_set("display_errors", true);
error_reporting(E_ALL | E_STRICT);

class dinomitedys_upload
{
    const rrw_dino = "wpprrj_00rrwdinos";


    public static function upload($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debug = true;
        $wpdbExtra = new wpdbExtra;
        $selected = rrwUtil::fetchparameterString("selected");
        $plugDire = "/wp-content/plugins/dinomitedasys";
        $jsFile = "$plugDire/dropzone.js";
        $cssFile = "$plugDire/dropzone.css";

        $msg .= "
<link rel='stylesheet' id='dropzone-css'  href='$cssFile' />

        ";


        $msg .= "<form method=\"post\" action=\"/upload-process/\" enctype=\"multipart/form-data\" > 
        ";
        $sql = "select * from " . self::rrw_dino . " order by name ";
        $recs = $wpdbExtra->get_resultsA($sql);
        //      $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";
        $msg .= "<table style=\"border-collapse: collapse; \">
            <tr class=\"freewheel_td\" >
            <td style=\"vertical-align:middle; \">
        ";
        $msg .= '<select id="dino" name="dino" onchange="showPicture( this.value );" >\n";
        if (empty($selected))
        $msg .="<option value="" disabled selected >Pick a dinosaur. </option>\n';
        foreach ($recs as $rec) {
            $name = $rec["Name"];
            $file = $rec["Filename"];
            $msg .= '<option value="' . $file . '"';
            if ($selected == $name)
                $msg .= " selected=selected ";
            $msg .= "> $name </option>\n";
        }
        $msg .= "</select> 
            </td>\n<td>
                <div id='pic1' > <img src='/graphics/white.gif' height='150px'
                </div>
            </td></tr></table> ";

        $limit = 40;
        $size = $limit + 20;
        $msg .= "
        <table>
        <tr>
            <td class=\"freewheel_td\" >
                <strong>Location Description:</strong> This should help a user to locate the dinosaur. 
                <br> &nbsp; &nbsp;Such as a street address or
                <br> &nbsp; &nbsp;building name with guide to where inside.<br \><br \>
                <input type='text' maxlength='$limit' size='$size' 
                    name='locationDesc'  id='locationDesc'
                   onkeyup='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onkeydown='countChars(\"locationDesc\",\"locationLeft\", $limit);'
                   onmouseout='countChars(\"locationDesc\",\"locationLeft\", $limit);' />
                <br> &nbsp; &nbsp; &nbsp; &nbsp; 
                <span id=\"locationLeft\">$limit</span> Characters left </td>
                
            <td class=\"freewheel_td\" >
               <strong>Location Cordinates:</strong> can be determined from a  photgraph taken with a device that has location turned on. 
               <table><tr><td width=\"60 px\" >
               ";
        $msg .= self::dropzone_div("coordinates");
        $msg .= "
);                     </td><td>Drop file or click to upload</td></td>
                </tr>
                </table>
            </td> 
        </tr>
       </table> 
       Pictures to be include in the full deescription should be dropped here or click to add.
       Take pictures from various side of the dinasaur. Aviod  shots the show the same image.</td> 
               <table><tr>
       ";
        for ($ii = 0; $ii < 6; $ii++) {
            $msg .= self::dropzone_div("picture$ii");;
        }
        $msg .= "
                </tr>
                </table>
       <br/>
       <input type=\"submit\" value=\"Click to process this data\" />
 
  <script src=\"$jsFile\"></script>
       </form>
    ";


        $msg .= "$errorBeg upload not yet implmented $errorEnd";


        $msg .= ' 
 <script>
    place = document.getElementById("dino");
    place.onchange();
</script> 
    ';
        return $msg;
    } // end upload

    static private function dropzone_div($name)
    {
        $msg = "";
        $msg .= "
                <td>
                    <div class=\"drop-zone\"
                    ondragstart=\"dropzoneDragOver(this);\" 
                    ondragover=\"dropzoneDragOver(this);\" >
                        <span class=\"drop-zone__prompt\"></span>
                            <input type=\"file\" name=\"$name\" id=\"$name\" class=\"drop-zone__input\">
                    </div> 
                </td>
                    ";
        return $msg;
    }

    static public function process_upload($atr)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debugSave = true;

        if ($debugSave) {
            $msg .= rrwUtil::print_r($_POST, true, "What was gottem by the submit _post");
            $msg .= rrwUtil::print_r($_FILES, true, "the files_files");
        }
        $basedir = "/home/pillowan/www-dinomitedays"; // get_home_path();
        $images = "$basedir/designs/images";

        $dino = rrwUtil::fetchparameterString("dino");

        if (empty($dino)) {
            return "$msg $errorBeg #790 missing the dinosaur seletion $errorEnd";
        }
        $dino = $_POST["dino"];
        for ($next_number = 1; $next_number < 20; $next_number++) {
            $newfile = "{$dino}next_number.jpg";
            if (!file_exists("$images/$newfile"))
                break;
        }
        $existing_number = $next_number - 1;
        $msg .= "There are $existing_number files already on the 
                <a href='/designs/$dino' >dinosaur's page</a> $eol";
        $uploads_dir = '/home/pillowan/www-dinomitedays/wp-content/uploads/newpictures';

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

            if ($error == UPLOAD_ERR_OK) {
                if ("coordinates" == $key)
                    $number = "";
                else {
                    $existing_number++;
                    $number = $existing_number;
                }
                $newname = "$uploads_dir/" . $dino . "$number" . "_$key" . "_$filename";
                $answer = move_uploaded_file($tmp_name, $newname);
                if ($debugSave) $msg .= "moving $tmp_name  to $newname $eol";
                if (false === $answer) {
                    $msg .= "$errorBeg E#781 there was a problem in retrieving/move the file $newname $errorEnd ";
                } else {
                    $msg .= "I#794 moved file to $newname $eol";
                    if ("coordinates" != $key) {
                        $ii = strrpos($filename, ".");
                        $finalname = "$images/$dino" . ($existing_number - 1) . substr($filename, $ii);
                        $msg .= "copy( $newname, $finalname )$eol";
                        $answer = copy($newname, $finalname);
                        if (false === $answer) {
                            $msg .= "$errorBeg E#781 there was a problem in copying the fileto $finalname $errorEnd ";
                        } else {
                            $msg .= "I#810 copy file to $newname $eol";
                        }
                    }
                }
            } else {
                $msg .= "$errorBeg error $error uploading the file $filename $errorEnd";
                continue;
            }
        } //  end foreash ($files)
        $msg .= $eol;
        return $msg;
    }
} // end class
