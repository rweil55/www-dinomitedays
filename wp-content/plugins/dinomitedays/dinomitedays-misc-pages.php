<?php

ini_set("display_errors", true);
error_reporting(E_ALL | E_STRICT);

class dinomitedays_misc_pages
{
    const rrw_dinos = "wpprrj_00rrwdinos";
    const siteDir = "/home/pillowan/www-dinomitedays/";
    const imagePath = "designs/images";
    const imageDire = self::siteDir . self::imagePath;
    const http = "https://dinomitedays.org/";

    public static function last_seen($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debugLast = true;

        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL | E_STRICT);
            $msg = "";
            $lastOrkey = rrwPara::String("lastorkey", $attr, "last");
            $msg .= "lastorkey: $lastOrkey $eol";
            $sql = "select keyid,  name, status, filename, mapdate, 
                    maploc, latitude, longitude 
                    from " .  self::rrw_dinos;
            if (strcmp("last", $lastOrkey) == 0)
                $sql .= " order by year(mapDate)  desc, name asc ";
            else
                $sql .= " order by keyid";
            if ($debugLast) $msg .= "$sql $eol";
            $recs = $wpdbExtra->get_resultsA($sql);
            if ($debugLast) $msg .= "$sql &nbsp; found " . $wpdbExtra->num_rows . " records $eol ";

            $yearPast = "not yet";
            foreach ($recs as $rec) {
                $name = $rec["name"];
                $status = $rec["status"];
                $filename = $rec["filename"];
                $mapdate = $rec["mapdate"];
                $maploc = $rec["maploc"];
                $latitude = $rec["latitude"];
                $longitude = $rec["longitude"];
                $keyid = $rec["keyid"];

                $mapYear = new DateTime($mapdate);
                $mapYear = $mapYear->format("Y");
                if ($mapYear != $yearPast && strcmp("last", $lastOrkey) == 0)
                    $msg .= "<span style='font-weight:bold; ' > $mapYear </span>$eol";
                $yearPast = $mapYear;
                $displayName = "$keyid $mapYear $name";
                if (!empty($status))
                    $displayName .= " $status ";
                if (0 == $latitude)
                    $displayMap = "";
                else
                    $displayMap = "$maploc
                    <a href='/map/?dino=true&latitude=$latitude&longitude=$longitude' > map</a>";
                $msg .= "<a href='/designs/$filename.htm' > $displayName</a>
                                    $displayMap $eol";
            }
        } catch (Exception $ex) {
            throw new Exception("$msg $errorBeg E#763 dinomitedys_upload:upload: $errorEnd");
        }
        return $msg;
    } // end last_seen

} // end class
