<?php

class DisplayThumbnails
{
    public static function Display($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos, $rrw_photographers;
        $msg = "";
        ini_set("dislay_errors", true);
        $listdisplay = false;

        $msg .= "dinomitedays-thumbnails: $eol";
        $msg .= '<link rel="stylesheet" type="text/css" href="images/pictures.css" />' . $eol;
        $sql = "select keyid,  name, status, filename, mapdate, 
                    maploc, logoFileName 
                    from $rrw_dinos
                    order by name";
        $msg .= "sql: $sql $eol";
        $recs = $wpdbExtra->get_resultsA($sql);
        if ($listdisplay)
            $msg .= '<ul class="rrwPhotoGrid" role="list">';

        foreach ($recs as $rec) {
            $name = $rec["name"];
            $filename = $rec["filename"];
            $logoFileName = $rec["logoFileName"];
            if (empty($logoFileName)) {
                $logoFileName = "N/A";
            }
            if ($listdisplay) {
                $msg .= "<li class='rrwPhotoGrid'><a href='/designs/$filename.htm' > 
                    <img src='/graphics/$logoFileName' width='135' height='125' 
                    alt='$name || $logoFileName' ></a></li>\n";
            } else {
                $msg .= "<a href='/designs/$filename.htm' > 
                    <img src='/graphics/$logoFileName' width='135' height='125' 
                    alt='$name || $logoFileName' ></a>\n";
            }
        }
        $msg .= "</ul>\n";
        return $msg;
    }
}
