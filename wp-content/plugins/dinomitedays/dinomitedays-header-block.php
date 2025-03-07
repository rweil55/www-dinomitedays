<?php

class dinomitedays_header_block
{
    private static $wpdbExtra;
    function __construct()
    {
        self::$wpdbExtra = new wpdbExtra;
    }
    public static function header($attr)
    {
        global $eol;
        global $wpdbExtra;
        $msg = "";
        $debug = false;
        $msg .= "
    <style>
        .headerItalic {
            font-style: italic;
            color: #006600;
            weight: bold;
        }
    </style>
    ";
        $dinoName = rrwParam::String('name', $attr);
        $sql = "select * from wpprrj_00rrwdinos where Name = '$dinoName'";
        if ($debug)  $msg .= "$sql $eol";
        $dinos = $wpdbExtra->get_resultsA($sql);
        $dino = $dinos[0];
        $dinoOldName = $dino['Oldname'];
        $sponsor = $dino["Sponsor"];
        $mapLoc = $dino["Maploc"];
        $latitude = $dino["Latitude"];
        $longitude = $dino["Longitude"];
        $auctionPrice = $dino["ActionPrice"];
        $charity = $dino["Charity"];
        $theme = $dino["Theme"];
        $materials = $dino["Material"];
        if (0 == $latitude || 0 == $longitude) {
            $directionsTo = "";
        } else {
            $directionsTo = "<a href='https://www.google.com/maps/dir//$latitude,$longitude' target='map' > directions to </a>";
        }
        $msg .=
            self::oneLine("Sponsored by: $sponsor") .
            self::oneLine("Charity: $charity");
        if (!empty($mapLoc)) {
            $msg .= self::oneLine("Fossil Location: $directionsTo $mapLoc");
        }
        if (!empty($auctionPrice)) {
            $msg .= self::oneLine("Auction: $auctionPrice");
        }
        $msg .=
            self::oneLine("Theme: $theme ") .
            self::oneLine("Current Materials: $materials");
        if (!empty($dinoOldName)) {
            $msg .= "The original dinosaur <strong>$dinoOldName</strong> was retired and replaced by
                    <a href='$dinoName.htm' > <strong>$dinoName</strong></a>$eol";
            $sql = "select * from wpprrj_00rrwdinos where Name = '$dinoOldName'";
            $dinos = $wpdbExtra->get_resultsA($sql);
            $dino = $dinos[0];
            $oldsponsor = $dino["Sponsor"];
            $oldauctionPrice = $dino["ActionPrice"];
            $oldcharity = $dino["Charity"];
            $oldtheme = $dino["Theme"];
            $oldmaterails = $dino["Material"];

            $msg .=
                self::oneLine("Original Sponsored by: $oldsponsor") .
                self::oneLine("Original Charity: $oldcharity") .
                self::oneLine("Original Auction: $oldauctionPrice") .
                self::oneLine("Original Theme: $oldtheme ") .
                self::oneLine("Original  Materials: $oldmaterails");
        }
        return $msg;
    }
    private static function oneLine($labelvalue)
    {
        global $eol;
        $iiColon = strpos($labelvalue, ":");
        if (false === $iiColon) {
            return "<span class='headerItalic' >$labelvalue</span>$eol";
        }
        $header = substr($labelvalue, 0, $iiColon);
        $value = trim(substr($labelvalue, $iiColon + 1));
        if (empty($value)) {
            return "<span class='headerItalic' >$header:</span>$eol";
        } else {
            return "<span class='headerItalic' >$header: </span>$value$eol";
        }
    }
} //end class
