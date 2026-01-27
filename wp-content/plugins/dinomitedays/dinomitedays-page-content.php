<?php
class dinomitedays_Page_Content
{
    public static function render_page_content($attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;

        $eol = "\n";
        $msg = "";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $cssFile = "https://dinomitedays.org/wp-content/plugins/dinomitedays/dinomitedays.css";
        print "<link rel='stylesheet' id='dinomitedays-css'  href='$cssFile' />";
        $editor_id = "my-content-wp-editor";

        $dino = rrwParam::String('dino',   $attributes);
        if (empty($dino)) {
            $msg .= dinomitedays_upload::buildDinoSelectionForm($attributes);
            $msg .= $errorBeg . "Error: 'dino' parameter is required." . $errorEnd . $eol;
            return $msg;
        }
        $sqlDino = "select * from $wpdbExtra->dinosaurs where dinoName = '$dino' or fileName = '$dino'";
        $dinoData = $wpdbExtra->get_resultsA($sqlDino);

        if (! $dinoData) {
            $msg .= $errorBeg . "Error: Dinosaur '$dino' not found." . $errorEnd . $eol;
            return $msg;
        }
        if (1 != $wpdbExtra->num_rows) {
            $msg .= $errorBeg . "Error: Multiple entries found for dinosaur '$dino'." . $errorEnd . $eol;
            return $msg;
        }
        $dinoData = $dinoData[0];
        $FileName = $dinoData['fileName'];
        $msg .= rrwUtil::print_r($dinoData, true, "I#1389 Dino Data") . $eol;

        if (! empty($_POST[$editor_id])) {
            // submit was pressed, now update the database
            $contentNew = $_POST[$editor_id];  // then new data
            $contentNew = str_replace("'", "'", $contentNew);
            $sqlUpdate = "update $wpdbExtra->dinosaurs set pageContent = '$contentNew' where fileName = '$FileName'";
            $msg .= "I#1390 SQL Update: $sqlUpdate $eol";
            $wpdbExtra->query($sqlUpdate);
            $msg .= "Content submitted: $contentNew $eol";
            return $msg;
        }
        // have Dino, no new content, so show the form with the current content
        $contentCurrent = $dinoData['pageContent'];
        $msg .= $wpdbExtra->insertIntoHistory($dino, $contentCurrent);

        $errorBeg = "<div class='error'>";
        $errorEnd = "</div>";
        $msg = "";
        $msg .= rrwUtil::print_r($_GET, true, "GET") . $eol;
        $msg .= rrwUtil::print_r($_POST, true, "POST") . $eol;
        echo "<form method='post' >";
        // $msg .= rrwUtil::print_r($contentCurrent, true, "I#1388 Current Content") . $eol;

        wp_editor($contentCurrent, $editor_id,  array('textarea_name' => $editor_id, 'media_buttons' => false, 'editor_height' => 350, 'teeny' => true));
        echo '
        <input type="hidden" name="dino" value="' . $dino . '" />
        <input type="submit" value="Accept the changes that I made to the content" />
        </form>';

        return $msg;
    } // end function render_page_content

} // end class dinomitedays_Page_Content
