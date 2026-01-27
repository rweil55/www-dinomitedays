<?php
class dinomitedays_header_block
{
    private static $wpdbExtra;
    function __construct()
    {
        self::$wpdbExtra = new wpdbExtra;
    }
    /**
     * Generates HTML content for a dinosaur header block display.
     *
     * This method retrieves dinosaur information from the database based on the provided name
     * and formats it into an HTML display including sponsor details, charity information,
     * location data with Google Maps integration, auction prices, themes, and materials.
     * If the dinosaur has been replaced, it also displays information about the original dinosaur.
     *
     * @param array $attr Attributes array containing:
     *                   - 'name' (string): The name of the dinosaur to retrieve information for
     *
     * @return string HTML formatted content including:
     *                - CSS styles for italic header formatting
     *                - Sponsor information
     *                - Charity details
     *                - Fossil location with Google Maps directions link (if coordinates available)
     *                - Auction price (if available)
     *                - Theme and materials information
     *                - Original dinosaur information (if this is a replacement)
     *
     * @global string $eol End of line character for formatting
     * @global object $wpdbExtra Extended WordPress database object for custom queries
     *
     * @uses rrwParam::String() To safely extract the 'name' parameter from attributes
     * @uses self::oneLine() To format individual lines of dinosaur information
     *
     * @since 1.0.0
     */
    public static function createHeaderBlock($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;
        $msg = "";
        $debug = false;
        $msg .= "
    <style>
        .headerItalic {
            font-style: italic;
            color: #006600;
            font-weight: bold;
        }
    </style>
    ";
        $dinoName = rrwParam::String('name', $attr);
        $sql = "select * from wpprrj_00rrwdinos where Name = '$dinoName'";
        if ($debug)  $msg .= "$sql $eol";
        $dinos = $wpdbExtra->get_resultsA($sql);
        if ($wpdbExtra->num_rows == 0) {
            $msg .= "$errorBeg Dinosaur record for $dinoName not found. $errorEnd";
            return $msg;
        }
        $dino = $dinos[0];
        $dinoOldName = $dino['Oldname'];
        $sponsor = $dino["Sponsor"];
        $mapLoc = $dino["mapLoc"];
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
        if (empty($dinoOldName)) {
            $msg .= self::oneLine("Original dinosaur record not found.");
            return $msg;
        }
        $msg .= "The original dinosaur <strong>$dinoOldName</strong> was retired and replaced by
                <a href='$dinoName.htm' > <strong>$dinoName</strong></a>$eol";
        //
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
        return $msg;
    }  // end createHeaderBlock
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
