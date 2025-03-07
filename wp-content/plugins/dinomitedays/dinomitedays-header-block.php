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
            weight: bold;
        }   
    </style>
    ";
        $dinoName = rrwPara::String('name', $attr);
        $sql = "select * from wpprrj_00rrwdinos where Name = '$dinoName'";
        if ($debug)  $msg .= "$sql $eol";
        $dinos = $wpdbExtra->get_resultsA($sql);
        $dino = $dinos[0];
        $dinoOldName = $dino['Oldname'];
        $sponsor = $dino["Sponsor"];
        $maploc = $dino["Maploc"];
        $latitude = $dino["Latitude"];
        $longitude = $dino["Longitude"];
        $auctionPrice = $dino["ActionPrice"];
        $charity = $dino["Charity"];
        $theme = $dino["Theme"];
        $materails = $dino["Material"];

        $msg .=
            self::oneline("Sponsored by: $sponsor") .
            self::oneline("Charity: $charity") .
            self::oneline("Fossil Location: $maploc") .  // build mapping links here
            self::oneline("Auction: $auctionPrice") .
            self::oneline("Theme: $theme ") .
            self::oneline("Current Materials: $materails");
        if (!empty($dinoOldName)) {
            $msg .= "The original dinosaurer '$dinoOldName' was retired and replaced by '$dinoName'$eol";
            $sql = "select * from wpprrj_00rrwdinos where Name = '$dinoOldName'";
            $dinos = $wpdbExtra->get_resultsA($sql);
            $dino = $dinos[0];
            $oldsponsor = $dino["Sponsor"];
            $oldauctionPrice = $dino["ActionPrice"];
            $oldcharity = $dino["Charity"];
            $oldtheme = $dino["Theme"];
            $oldmaterails = $dino["Material"];

            $msg .=
                self::oneline("Original Sponsored by: $oldsponsor") .
                self::oneline("Original Charity: $oldcharity") .
                self::oneline("Original Auction: $oldauctionPrice") .
                self::oneline("Original Theme: $oldtheme ") .
                self::oneline("Original  Materials: $oldmaterails");
        }
        return $msg;
    }
    private static function oneline($labelvalue)
    {
        global $eol;
        $data  = explode(":", $labelvalue);
        if (empty(trim($data[1]))) {
            return "";
        }
        return "<span class='headerItalic' >$data[0]: </span>$data[1]$eol";
    }
} //end class
