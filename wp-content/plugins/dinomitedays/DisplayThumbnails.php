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

        $msg .= "<form method='post' action=''>\n";
        foreach (array("stegosaurus" => "sq_s%", "torosaurus" => "sq_t%", "t-rex" => "sq_r%") as $type => $logoPrefix) {
            $checked = (isset($_POST['dinoType']) && $_POST['dinoType'] == $type) ? "checked" : "";
            $msg .= "<input type='checkbox' name='$type' value='$type' $checked onchange='this.form.submit()'> $type \n";
            $item = rrwParam::Boolean($type);
            if ($item) {
                $sqlWhere .= " logoFilename like '$logoPrefix' or";
            }
        }
        if (!empty($sqlWhere)) {
            $sqlWhere = " where " . substr($sqlWhere, 0, -3); // remove the last 'or'
        }
        $msg .= "</form>\n";
        $sql = "select keyId,  dinoName, status, filename, mapDate,
                    mapLoc, logoFileName
                    from $wpdbExtra->dinosaurs
                    $sqlWhere
                    order by dinoName";
        $msg .= "sql: $sql $eol";
        $recs = $wpdbExtra->get_resultsA($sql);
        $msg .= "Number of records found: " . $wpdbExtra->num_rows . " $eol";
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
        return $msg;
    }
}
