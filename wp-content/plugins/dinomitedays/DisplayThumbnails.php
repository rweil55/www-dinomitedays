<?php

class DisplayThumbnails
{
    public static function Display($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_photographers;
        $msg = "";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $listDisplay = false;
        $sqlWhere = "";
        $typeArray = array("stegosaurus" => "sq_s%", "torosaurus" => "sq_t%", "t-rex" => "sq_r%");

        $typeIn = rrwParam::string("type", $attr, "");
        $showSelection = "<h2> Select a type: ";
        foreach ($typeArray as $key => $value) {
            $showSelection .= "<a href='?type=$key'";
            if ($typeIn == $key) {
                $showSelection .= " class='selected'";
            }
            $showSelection .= " >$key</a> &nbsp; ";
        }
        $showSelection .= "</h2>\n";

        switch ($typeIn) {
            case "stegosaurus":
                $learnMore = "or You can also <a href='/steg_about.htm'>learn about Stegosaurus</a>!";
                break;
            case "torosaurus":
                $learnMore =  ""; // "or You can also <a href='/torosaurus_about.htm'>learn about Torosaurus</a>!";
                break;
            case "t-rex":
                $learnMore = "or You can also <a href='/rex_about.htm'>learn about Tyrannosaurus rex</a>!";
                break;
            default:
                $learnMore = "";
        }
        $msg .= "$showSelection &nbsp; &nbsp; $learnMore $eol";
        if (!empty($typeIn)) {
            $logoPrefix = $typeArray[$typeIn];
            $sqlWhere = " where   logoFilename like '$logoPrefix' ";
        } else {
            $sqlWhere = "";
        }
        $sqlWhat = dinomitedays_misc_pages::dinoSglSelect . " $wpdbExtra->dinosaurs
                        $sqlWhere
                        order by dinoName";
        // $msg .= "sql: $sql $eol";
        $msg .= dinomitedays_misc_pages::gridDisplay($sqlWhat,  "nameOnly");
        /*
        $recs = $wpdbExtra->get_resultsA($sql);
        // $msg .= "Number of records found: " . $wpdbExtra->num_rows . " $eol";
        $msg .= "
        <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
            .grid-item {
            text-align: center;
        }
            </style>
        ";

        $msg .= '<div class=grid>';


        foreach ($recs as $rec) {
            $name = $rec["dinoName"];
            $filename = $rec["filename"];
            $logoFileName = $rec["logoFileName"];
            if (empty($logoFileName)) {
                $logoFileName = "N/A";
            }

            $msg .= "<div class='grid-item'><a href='/designs/$filename.htm' >
                    <img src='/graphics/$logoFileName' width='130''
                    alt='$name || $logoFileName' ></a><br>$name</div>\n";
        }
        $msg .= "</div>\n";
        */
        return $msg;
    }
}
