<?php
class freewheelAPI {
    const googleMapsAPIkeyList = array(
        // under the roy all mail account // linked billing account
        "demo1.royweil" => "AIzaSyCrKTXYSZvl59mlWrfKxGk8mYvFJkl3JKA",
        "dinomitedays.org" => "AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo",
         "edit.shaw-weil" => "AIzaSyCrKTXYSZvl59mlWrfKxGk8mYvFJkl3JKA",
  "edit.shaw-weil" => "AIzaSyCu4ddeY4GRlVKcDUWxs1Hd9tgVj_KTv9M",
        "edit.shaw-weil" => "AIzaSyBUvagxH2HnWmshmiX0Hg361Yv3SynmQ_U",
     
        "freewheelingeasy.com" => "AIzaSyCDJVJzOv-c6d6zHiXdXlXi8EY5wHQqQu0",
        "2020.eriepittsburghtrail" => "AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo",
        "private.eriepittsburghtrail" => "AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo",
        "pgherie.com" => "AIzaSyBkOnueISCedsKqOyrXpL9KMAMF5jNpizY",
        "redbank.royweil" => "AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo",
        "steelvalleytrail.org" => "AIzaSyC5OcdhkgIJG8Y1XF4N5rbva7mOoOgh_fQ",
        "dev.steelvalleytrail" => "AIzaSyC5OcdhkgIJG8Y1XF4N5rbva7mOoOgh_fQ",
        "wp1.steelvalleytrail" => "AIzaSyC5OcdhkgIJG8Y1XF4N5rbva7mOoOgh_fQ",
        "steelvalleytrail.stage" => "AIzaSyC5OcdhkgIJG8Y1XF4N5rbva7mOoOgh_fQ",
        // misc
        "artincc.org" => "AIzaSyDtz1B45L0o57bawrhn16z-IDWjk7m8Ylg",
        "API key2" => "AIzaSyBxx8E2H6PqX6MWaa1hbnKv_R1HBwyppcU",
        "Browser key 1" => "AIzaSyAlky9rjhWm0bWkl8eE8WuELa2eiSQbizI",
        "deleted" => "AIzaSyAxxBBWpSqHpDghUr5h2nRWwWkjoB_yF3g", // del
        "deleted" => "AIzaSyBJ2CpHjr91iYwubzEqrjnkZtfaVQyDn48", // del
        "xxx" => "AIzaSyCo0_Gq2nmfW_XoO-nMEQ_9eEmHxtoe9-c",
        "they-working.org" =>"AIzaSyBiNDs8g_nVKfm7jOXO1nnJu9H0koc2f-A",
 
    );
    const googleApi_bill = array(
        //                         apikey  billed to VVVV
        // bill code 1
        "AIzaSyCu4ddeY4GRlVKcDUWxs1Hd9tgVj_KTv9M" => "Edit-1",
        "AIzaSyBUvagxH2HnWmshmiX0Hg361Yv3SynmQ_U" => "Edit-2",
        "AIzaSyCrKTXYSZvl59mlWrfKxGk8mYvFJkl3JKA" => "Edit-3",
        "AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo" => "Other users",
        "AIzaSyC5OcdhkgIJG8Y1XF4N5rbva7mOoOgh_fQ" => "Steel Valley Billing",
        "AIzaSyDtz1B45L0o57bawrhn16z-IDWjk7m8Ylg" => "artincc",
        "AIzaSyCDJVJzOv-c6d6zHiXdXlXi8EY5wHQqQu0" => "freewheeling easy",
        "AIzaSyBiNDs8g_nVKfm7jOXO1nnJu9H0koc2f-A" => "They Working funded",
    );
    static public
    function getAPIkey( $task = "" ) {
        global $eol, $errorBeg, $errorEnd;
        /* not working 2020-05-22
         *   AIzaSyAuNXpAR_dsGK8Q03QhEHowq93qVISqUUo
         *   AIzaSyCrKTXYSZvl59mlWrfKxGk8mYvFJkl3JKA
         */
        /*
        BILLING ACCOUNTA   PROJECTS
        edit-pgherie        none
        eriePGH-2           demo1, erie pittsburgh, edit-pgherie
        other               redbank, steel Vwllwy Development, FreeWheeling Easy, Steel Valley
        Google Cloud 01C842-B579BE-F70333 editpgherie
        Google Cloud 01CCBE-0EBDE3-31CC20 other users
        Google CLOUD 01C842-B5 INTERNET CA 94043 US capital one 29.79 12/2/2019
        */
        $debugKeyAssign = false;
        $site = site_url();
        if ( $debugKeyAssign ) print "site url $site, usingg key $key $eol";
        $site = substr( $site, 8 ); // remove https://
        if ( $debugKeyAssign ) print "site substr $site, usingg key $key $eol";
        $iiDot = strpos( $site, "." ); // first period
        if ( $debugKeyAssign ) print "iiDot $iiDot";
        $iiDot = strpos( $site, ".", $iiDot + 1 ); // second periond
        if ( $iiDot === false )
            $iiDot = strlen( $site );
        $site = substr( $site, 0, $iiDot ); // xxx.xxx
        if ( $debugKeyAssign ) print "site $site, usingg key $key $eol";
        $site = strtolower( $site );
        if ( $debugKeyAssign ) print "site $site, usingg key $key $eol";
        // got site name 
        if ( "edit.shaw-weil" == $site || "demo1.royweil" == $site )
        //  special case with lots of action
            $key = get_option( "FreewheelinAPIkey",
            self::googleMapsAPIkeyList[ "edit.shaw-weil" ] );
        elseif ( array_key_exists( $site, self::googleMapsAPIkeyList ) )
            $key = self::googleMapsAPIkeyList[ $site ];
        else
            $key = "Missing_Google_Key_for_the_website $site";
        if ( $debugKeyAssign ) print "site $site, using key $key $eol";
        return "key=$key";
    }
    static public function getBillingName( $apiKey ) {
        // returns the google billinf name given an apiKey
        if ( array_key_exists( $apiKey, self::googleApi_bill ) )
            return self::googleApi_bill[ $apiKey ];
        else
            return $apiKey;
    } // end function getBillingName
    static public function getBillingApiByName( $apiName ) {
        // returns the google billinf name given an apiKey
        foreach ( self::googleApi_bill as $key => $value ) {
            if ( $value == $apiName )
                return $key;
        }
        return $value;
    } // end functin getBillingApiByNAme
    
} // end class freewheelAPI
?>