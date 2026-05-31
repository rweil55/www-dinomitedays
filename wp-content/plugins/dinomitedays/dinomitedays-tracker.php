<?php

class dinomitedays_tracker
{
    private static $color = "#000000";

    public static function tracker(array $attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debugTrackerInput = false;

        if ($debugTrackerInput) {
            $msg .=  rrwUtil::print_r($attributes, true, "Attributes passed to tracker");
            $msg .=  rrwUtil::print_r($_GET, true, "get passed to tracker");
            $msg .=  rrwUtil::print_r($_POST, true, "post passed to tracker");
            $msg .= get_option("dinomitedays_dino") . " is the dino file from options table $eol";
        }
        $dinoFile = "";
        $msg .= dinomitedays_upload::buildDinoSelectionForm($dinoFile);
        if (empty($dinoFile)) {
            return $msg;
        }
        if ($debugTrackerInput) $msg .= "I#1402 Dinosaur file selected: $dinoFile $eol";
        $comment = rrwParam::String('comment', $attributes); // Get the comment content from the form submission
        if (!empty($comment)) {
            if ($debugTrackerInput) $msg .= "I#1405 Comment submitted: $comment $eol";
            $current_user = wp_get_current_user();
            $username = $current_user->display_name;
            $color = rrwParam::String("color"); // get color from the posts);
            $newComment = "<span style=\'color:" . self::$color . "\'>" . date('Y-m-d') . " - " . $username . " - " . $comment . "</span>";
            $sqlComment = "update $wpdbExtra->dinosaurs set notes = CONCAT(notes, '$newComment$eol') where fileName = '$dinoFile'";
            $cntUpdated = $wpdbExtra->query($sqlComment);
            if ($debugTrackerInput) $msg .= "I#1406 Number of rows updated: $cntUpdated -- $sqlComment $eol";
            self::$color = rrwFormat::colorSwap(self::$color);
        }
        if (substr($dinoFile, 0, 12)  == "thumb - nail") {
            // special case for thumbnails where the file name is actually the dino type
            $dinoType = substr($dinoFile, 13);
        }
        if ($debugTrackerInput) $msg .= "I#1407 Dinosaur file after removing .htm extension if present: $dinoFile $eol";


        $sql = "select * from $wpdbExtra->dinosaurs where FileName = '$dinoFile'";
        $results = $wpdbExtra->get_resultsA($sql);
        if ($wpdbExtra->num_rows != 1) {
            $msg .= "E#1401 No dinosaur found with file name $dinoFile $eol";
            return $msg;
        }

        $dinoName = $results[0]['dinoName'];
        $msg .= self::display_comments($results[0]);
        if ($debugTrackerInput) $msg .= "I#1404 New comments entry is displayed here  $eol";
        if (is_user_logged_in()) {
            $msg .= self::buildCommentForm($dinoName, $dinoFile);
        } else {
            $msg .= rrwUtil::showLoginForm("to add a comment for $dinoName");
        }
        return $msg;
    }

    private static function display_comments(array $record): string
    {
        global $eol;
        $msg = "";
        // $msg .= "I#1403 Previous comments are displayed here $eol";
        $notes = $record['notes'];
        $iiColor = strrpos($notes, "color;");     // find the previous color used in the notes
        self::$color = $iiColor ? substr($notes, $iiColor + 6, 7) : "#000000";
        $dinoName = $record['dinoName'];
        $dinoFile = $record['fileName'];
        if (! empty($notes)) {
            $msg = "Comments for <a href='https://dinomitedays.org/designs/$dinoFile.htm'>$dinoName</a> $eol";
            $msg .= $notes . $eol;
        }
        return $msg;
    }
    private static function buildCommentForm(string $dinoName, string $dinoFile): string
    {
        global $eol;
        $msg = "";
        self::$color = rrwFormat::colorSwap(self::$color); // swap the color for the next comment
        $msg .= "Add a comment for <a href='https://dinomitedays.org/designs/$dinoFile.htm'>$dinoName</a> or
                    <a href='https://dinomitedays.org/update'>update the dinosaur information</a> $eol";
        $msg .= "<form method='post' >"; // Form submission to the same page\n
        $msg .= "<input type='hidden' name='dinoFile' value='$dinoFile'>\n"; // Hidden field to pass the dino file name
        $msg .= "<textarea name='comment' rows='7' cols='80' ></textarea><br>"; // Textarea for comment input
        $msg .= "<input type='hidden' name='color' value='" . self::$color . "'>\n"; // Hidden field to pass the previous color
        $msg .= "<input type='submit' value='Submit Comment'>\n"; // Submit button
        $msg .= "</form>
        ";
        return $msg;
    }
} // end class Dinomitedays_Comment