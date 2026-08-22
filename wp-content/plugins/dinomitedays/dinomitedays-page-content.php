<?php
class dinomitedays_Page_Content
{
    public static function render_page_content($attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $eol = "\n";

        $msg = "";
        $debugRenderPageContent = false;
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        if (rrwUtil::notAllowedToEdit("design_html", "all", false)) {
            $msg .= "$errorBeg E#1486 not allowed to edit $errorEnd";
            return $msg;
        }
        $editor_id = "my-content-wp-editor";

        $msg .= dinomitedays_upload::buildDinoSelectionForm($dino);

        $sqlDino = "select * from $wpdbExtra->dinosaurs where dinoName = '$dino' or fileName = '$dino'";
        $dinoData = $wpdbExtra->get_resultsA($sqlDino);

        if (! $dinoData) {
            $msg .= $errorBeg . "E#1332  Dinosaur '$dino' not found." . $errorEnd . $eol;
            return $msg;
        }
        if (1 != $wpdbExtra->num_rows) {
            $msg .= $errorBeg . "E#1396  Multiple entries found for dinosaur '$dino'." . $errorEnd . $eol;
            return $msg;
        }
        $dinoData = $dinoData[0];
        $FileName = $dinoData['fileName'];

        if (! empty($_POST[$editor_id])) {
            // submit was pressed, now update the database
            $contentNew = $_POST[$editor_id];  // then new data
            $contentNew = str_replace("'", "'", $contentNew);
            $contentNew = str_replace("\n\n<p", "<p", $contentNew);
            $contentNew = str_replace("\n", "", $contentNew);   // remove previously installed newlines

            $msg .= self::addClassAbout($contentNew);           // add class to the about strings
            $msg .= self::addClassParagraph($contentNew);
            $contentNew = str_replace("<p", "\n\n<p", $contentNew);   // add newlines before each paragraph

            $sqlUpdate = "update $wpdbExtra->dinosaurs set pageContent = '$contentNew' where fileName = '$FileName'";
            if ($debugRenderPageContent) $msg .= htmlspecialchars("I#1490 SQL Update: $sqlUpdate $eol");
            $recordUpdatedCount = $wpdbExtra->query($sqlUpdate);
            if ($debugRenderPageContent) $msg .= "Content submitted: Update $recordUpdatedCount record(s)" . $eol;
            $historyComment = "Page content updated new content length = " . strlen($contentNew) . $contentNew;
            $msg .= $wpdbExtra->insertIntoHistory($dino, $historyComment);

            return $msg;
        }
        // have Dino, no new content, so show the form with the current content
        $contentCurrent = $dinoData['pageContent'];
        ob_start();
        wp_editor($contentCurrent, $editor_id,  array('textarea_name' => $editor_id, 'media_buttons' => false, 'editor_height' => 350, 'editor_width' => 700, 'teeny' => true));
        $editor_html = ob_get_clean();

        $msg .= "<form method='post' >";
        // $msg .= rrwUtil::print_r($contentCurrent, true, "I#1388 Current Content") . $eol;
        ob_start();
        wp_editor($contentCurrent, $editor_id,  array('textarea_name' => $editor_id, 'media_buttons' => false, 'editor_height' => 350, 'editor_width' => 1200, 'teeny' => true));
        $editor_html = ob_get_clean();
        $editor_html = str_replace('cols="40"', 'cols="120"', $editor_html);
        $msg .= $editor_html;
        $msg .= '<input type="hidden" name="dino" value="' . $dino . '" />
        <input type="submit" value="Accept these changes" />
        </form>';
        $msg .=  "<P>" . htmlspecialchars("<p class='dino-page-about'>About the Location<br><span class='dino-page-content'>xxxxxx is on a private residence.
        Please respect personal property. Do not leave the road to approach the dinosaur.</span></p>") .
            "</p><p>" . htmlspecialchars("<p class='dino-page-about'>About the Location<br><span class='dino-page-content'>xxxxx is inside a place of business.
         Please be respectful and do not cause any disruption.</span></p>") .
            "</p><p>" . htmlspecialchars("<p class='dino-page-about'>About the Location<br><span class='dino-page-content'>xxxxx is on a private residence and is not visible from the road.
                Please respect personal property.
                Do not leave the road to approach the dinosaur without asking permission.</span></p>");

        return $msg;
    } // end function render_page_content

    private static function addClassAbout(string &$content): string
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "<pre>";
        $debugAddClassAbout = false;
        foreach (["About the Design", "About the Sponsor", "About the Artist", "About the Location"] as $aboutHeading) {
            $iiHead = stripos($content, $aboutHeading);
            if ($iiHead !== false) {
                if ($debugAddClassAbout) $msg .= "I#1481 Found heading '" . strtolower(substr($content, $iiHead + 10, 8)) . "' $eol";
                if ("artistic" == strtolower(substr($content, $iiHead + 10, 8))) {
                    $msg .= "I#1487 Found heading '$aboutHeading' but it is about the artist, so skipping adding class 'about' $eol";
                    continue; // it is "about the artistic", "about the artist"
                }
                if ($debugAddClassAbout) $msg .= "I#1484 Found heading '$aboutHeading' in content at position $iiHead, length content " . strlen($content) . $eol;
                $iiLeft = $iiHead - 1;
                $msg .= self::findStartTagGivenEndTag($content, $iiLeft);
                // --------------------------------------------------------------ran out of tags, insert class and look for next about
                if ($debugAddClassAbout)  $msg .= "I#1483 Inserting class 'about' at $iiLeft through $iiHead $eol";
                $content = substr($content, 0, $iiLeft) . "<p class=\'dino-page-about\'>" . substr($content, $iiHead);
                //                                         123456789012345678
                $iiRight = strpos($content, ">", $iiLeft); // move to the end of <p class='about'> tag`
                $iiRight = strpos($content, "<", $iiRight + 1); // move to the start of the next tag after <p class='about'>about..., this is the end of the heading tag
                $iiNewEnd = $iiRight;
                if ($iiRight === false) {
                    throw new Exception("$msg $errorBeg E#1493  Expected to find closing tag '&gt;' for heading '$aboutHeading' but not found $errorEnd . $eol");
                }
                $msg .= self::findEndTagGivenStartTag($content, $iiRight);
                $content = substr($content, 0, $iiNewEnd) . "<br><span class=\'dino-page-content\'>" . substr($content, $iiRight);
                $iiEndParagraph = strpos($content, "</p>", $iiNewEnd);
                if ($iiEndParagraph !== false) {
                    if (substr($content, $iiEndParagraph - 1, 1) != ">") {
                        $content = substr($content, 0, $iiEndParagraph) . "<span>" . substr($content, $iiEndParagraph);
                    }
                }
            } // end if found an about heading
        } // end foreach aboutHeading
        return "</pre>
        $msg";
    } // end function addClassAbout
    private static function findStartTagGivenEndTag(string $content, int &$iiLeft): string
    {
        global  $eol, $errorBeg, $errorEnd;
        $msg = "";     // --------------------------------------------------------------found a preceding end tag, remove all tags before this
        $debugFindStartTagGivenEndTag = false;
        // assert that substr($content, $iiLeft, 1) == ">";
        if (substr($content, $iiLeft, 1) != ">") {
            $msgErr = "$eol E#1497  Expected to find closing tag '&gt;' at position $iiLeft but found '" . substr($content, $iiLeft, 30) . "'";
            $msg .= rrwFormat::backtrace($msgErr);
            return $msg;
            throw new Exception("$msg");
        }
        $loopCnt = 0;
        while (true) {
            $loopCnt++;
            if ($loopCnt > 15) {
                $msg .= $errorBeg . "E#1491  Loop count exceeded while looking for start of tag for heading $errorEnd . $eol";
                break;
            }
            if ($debugFindStartTagGivenEndTag) $msg .= "I#1480 Found closing tag '>' at position $iiLeft, keep looking left for start of tag $eol";
            $tempContent = substr($content, 0, $iiLeft);
            if ($debugFindStartTagGivenEndTag) $msg .= "I#1489 length tempContent " . strlen($tempContent) . $eol;
            $iiLeft = strrpos($tempContent, "<"); // this is the start of a tag, so keep looking left
            if ($iiLeft === false) {
                throw new Exception("$msg $errorBeg E#1498  No start tag found for heading  $errorEnd . $eol");
            }
            if (substr($content, $iiLeft - 1, 1) != ">") {
                // the previous < character is not immediately preceded by >  i.e it is not ><, so we found the start of the tag, so stop looking left
                break;
            }
            if ($debugFindStartTagGivenEndTag) $msg .= htmlspecialchars("I#1482 Looking for start found char '<' at position $iiLeft $eol");
            if ($iiLeft == 0)
                break; // the starting of the tag string goes to the beginning of the content, so stop looking left
            $iiLeft--;  //  move one char to left of .lt.
            continue;
        }
        return $msg;
    } // end function findStartTagGivenEndTag to StartTag
    private static function findEndTagGivenStartTag(string $content, int &$iiRight): string
    {
        global  $eol, $errorBeg, $errorEnd;
        $msg = "";     // --------------------------------------------------------------found a preceding end tag, remove all tags before this
        // assert that substr($content, $iiRight, 1) == ">"
        if (substr($content, $iiRight, 1) != "<") {
            throw new Exception("$msg $errorBeg E#1494  Expected to find closing tag '>' at position $iiRight but found '" . substr($content, $iiRight, 1) . "' $errorEnd . $eol");
        }
        $loopCnt = 0;
        // $msg .= "<pre>";
        while (true) {
            $loopCnt++;
            if ($loopCnt > 15) {
                $msg .= $errorBeg . "E#1495  Loop count exceeded while looking for end of tag for heading." . $errorEnd . $eol;
                break;
            }
            $msg .= "Found open tag '<' at position $iiRight, keep looking right for end of tag $eol";
            $iiRight = strpos($content, ">", $iiRight); // this is the end of a tag

            if (substr($content, $iiRight + 1, 1) != "<") {
                $msg .= "E#1488 did not find '<' after the '>' so we done looking for the end of the tag $eol";
                // $iiRight points to an end tag >
                $iiRight++;
                break; // this is the end of the tag, so stop looking right
            }
            $iiRight++; // move one char to right of > it is an <, so we need to keep looking right for the end of the tag
            continue;
        }
        return $msg;
    } // end function findEndTagGivenStartTag to EndTag
    private static function addClassParagraph(string &$content): string
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debugAddClassParagraph = false;
        if ($debugAddClassParagraph) $msg .= "I#1485 Adding class 'dino-page-content' to all paragraphs  " . strlen($content);
        $content = str_replace("<p>", "<p class=\'dino-page-content\'\>", $content);
        if ($debugAddClassParagraph) $msg .= ",new length =" . strlen($content) . $eol;
        return $msg;
    } // end function addClassParagraph

} // end class dinomitedays_Page_Content
