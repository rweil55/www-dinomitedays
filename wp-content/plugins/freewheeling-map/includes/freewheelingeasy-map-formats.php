<?php
Class Freewheeling_format {
    public static function editlink( $id, $target = "" ) {
        // link to an edit given bizid, bizname, iconane,  linename
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_trails, $rrw_business, $rrw_icons, $rrw_lines;
        $sql = "select bizid, BizName from $rrw_business 
                    where $id - 'bizid' or $id = 'bizname'";
        $rec = $wpdbExtra->get_results( $sql );
        if ( 1 == $wpdbExtra->num_rows ) {
            $bizid = $rec[ 0 ][ "bizid" ];
            $BizName = $rec[ 0 ][ "BizName" ];
            $link = self::BizLink( $bizid, $BizName, $target );
            return $link;
        }
          $sql = "select iconid, iconName from $rrw_icons 
                    where $id = 'iconid' or $id = 'iconname'";
        $rec = $wpdbExtra->get_results( $sql );
        if ( 1 == $wpdbExtra->num_rows ) {
            $iconid = $rec[ 0 ][ "iconid" ];
            $iconName = $rec[ 0 ][ "iconName" ];
            $link = self::iconLink( $iconid, $iconName, $target );
            return $link;
        }
          $sql = "select lineid, lineName from $rrw_lines 
                    where $id = 'lineid' or $id = 'linename'"; 
        $rec = $wpdbExtra->get_results( $sql );
        if ( 1 == $wpdbExtra->num_rows ) {
            $lineid = $rec[ 0 ][ "lineid" ];
            $lineName = $rec[ 0 ][ "lineName" ];
            $link = self::lineLink( $lineid, $lineName, $target );
            return $link;
        }
           $sql = "select trailid, trName from $rrw_trails 
                    where $id = 'trailid' or $id = 'trname'";
        $rec = $wpdbExtra->get_results( $sql );
        if ( 1 == $wpdbExtra->num_rows ) {
            $trailid = $rec[ 0 ][ "trailid" ];
            $trailName = $rec[ 0 ][ "trName" ];
            $link = self::trailLink( $trailid, $trailName, $target );
            return $link;
        }
        return $id;
    } // end function editlink($id, $target = "")
  
    public static function BizMapLink( $bizid ) {
        return " <a target = 'map' href='/freewheelingeasy-google-map" .
        "?bizid=$bizid&allmileposts=on' >map</a> ";
    }
    public static function IconMapLink( $iconid ) {
        return " <a target='map' href='/freewheelingeasy-google-map" .
        "?iconid=$iconid&allmileposts=on&businesslocations=on&nohead=please' >map</a> ";
    }
    public static function TrailMapLink( $trailid ) {
        return " <a target = 'map' 
        href='/freewheelingeasy-google-map?trailid=$trailid&nohead=please' >map</a> ";
    }
    // this collection of routines outputs a pencil icon, and plain text
    public static function TrailLink( $trailid, $trailName = "", $target = "edit" ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_trails;
        if ( empty( $trailName ) )
            $trailName = $wpdbExtra->get_var( "select trName from $rrw_trails where trid = '$trailid' " );
        if ( $wpdbExtra->num_rows == 0 ) {
            return "$errorBeg E#982 trailName is blank and trail id of '$trailid' could
                    not find a match. $errorEnd";
        }
        return self::CommonLink( $trailid, $trailName, "trail", $target );
    }
    public static function IconNameLink( $iconName, $target = "edit" ) {
        global $wpdbExtra, $rrw_icons;
        $iconid = $wpdbExtra->get_var( "select iconid from $rrw_icons 
                                            where iconname = '$iconName' " );
        return self::CommonLink( $iconid, $iconName, "icon", $target );
    }
    public static function IconLink( $iconid, $iconName = "", $target = "edit" ) {
        global $wpdbExtra, $rrw_icons;
        if ( empty( $iconName ) )
            $iconName = $wpdbExtra->get_var( "select iconname from $rrw_icons where iconid = '$iconid' " );
        if ( is_null( $iconName ) || empty( $iconName ) )
            $iconName = "icon not found";
        return self::CommonLink( $iconid, $iconName, "icon", $target );
    }
    public static function BizLink( $bizid, $BizName = "", $target = "edit" ) {
        global $wpdbExtra, $rrw_business;
        global $eol, $errorBeg, $errorEnd;
        global $eol, $errorBeg, $errorEnd;
        if ( empty( $bizid ) && empty( $BizName ) )
            return "$errorBeg E#695 BizLink: no business name/id entered $errorEnd ";
        if ( empty( $BizName ) ) {
            $sqlBiz = "select BizName from $rrw_business where bizid = '$bizid' ";
            $BizName = $wpdbExtra->get_var( $sqlBiz );
            if ( $wpdbExtra->num_rows != 1 || is_null( $BizName ) ) {
                print formatBacktrace( "$errorBeg E#948 found " . $wpdbExtra->num_rows . "
                                    bizname in formatBizLink $eol $sqlBiz $eol" );
                $BizName = "Biz Name not found $errorEnd";
            }
        }
     	$sqlbizverified = "select bizverified from $rrw_business where bizid = '$bizid' ";
		$bizverified = $wpdbExtra->get_var($sqlbizverified);
	    return self::CommonLink( $bizid, $BizName, "biz", $target, $bizverified );
    }
    public static function ServiceLink( $svcid, $svcName = "", $target = "edit" ) {
        global $wpdbExtra, $rrw_business, $rrw_services;
        global $eol, $errorBeg, $errorEnd;
        if ( empty( $svcid ) && empty( $svcName ) )
            return "Unknown business";
        if ( empty( $svcName ) ) {
            $sqlSvc = "select SvcName from $rrw_services where svcid = '$svcid' ";
            $svcName = $wpdbExtra->get_var( $sqlSvc );
            if ( $wpdbExtra->num_rows != 1 ) {
                print formatBacktrace( "found " . $wpdbExtra->num_rows . "
                                    Svcname in formatBusinessLink $eol $sqlSvc $eol" );
                $svcName = "Service Name not found";
            }
        }
	   return self::CommonLink( $svcid, $svcName, "service", $target );
    }
    public static function lineLink( $lineid, $lineName = "", $target = "edit" ) {
        global $wpdbExtra, $rrw_lines;
        if ( empty( $lineName ) )
            $lineName = $wpdbExtra->get_var( "select lineName from $rrw_lines where lineid = '$lineid' " );
        return self::CommonLink( $lineid, $lineName, "line", $target );
    }
    public static function CommonLink( $itemid, $textOut, $action, $target,
									 $bizverified = "") {
  	global $rrw_pagesPreBuiltDire;
        global $eol, $errorBeg, $errorEnd;
        if ( empty( $itemid ) || empty( $textOut ) ) {
            throw new Exception( "$errorBeg 
                    E#981 either itrmid '$itemid' or textOut '$textOut' is blank $eol
                    Perhaps here is a region without a matching trail
                    $errorEnd" . formatBacktrace( "inside formatCommonLink" ) );
        }
         // display the edit pencil icon
        $itemNameLink = "https://edit.shaw-weil.com" .
			"/edit-$action/?${action}id=$itemid";
 		$itemNameLink = self::PencilIcon( $itemNameLink, $bizverified);
			
    // now the display thing
        if ( $textOut == 'View' ) {
            $itemNameLink = str_replace( "' target", "&view=please' target", $itemNameLink );
            $itemNameLink .= " $textOut </a> ";
        } else {
            $itemNameLink .= "  $textOut";
        }
        if ( $textOut == "Presque Isle Bay" )
            $itemNameLink = $textOut; // special case
        return $itemNameLink;
    }
	  public static function PencilIcon( $refLink, $BizVerified = "2002-01-01" ) {
        global $freewheelingeasy_images_URL;
        global $eol, $errorBeg, $errorEnd;
        $daysSinceLimit = get_user_option( "rrw_daysSinceLimit" );
        if ( $daysSinceLimit === false )
            $daysSinceLimit = 30;
        $days = freewheelingeasy_editbiz::daysSinceNow( $BizVerified );
        $iconUrlPath = "<img alt='Edit' src='$freewheelingeasy_images_URL/";
        if ( $days > $daysSinceLimit )
            $iconUrl = $iconUrlPath . "peniconred1.gif' />";
        else
            $iconUrl = $iconUrlPath . "penicon.gif' />";
        $temp = freewheeling_edit_setGlobals::editViewStart . "
							<a href='$refLink' target='edit' > $iconUrl</a> 
							" . Freewheeling_edit_setGlobals::editViewEnd;
        return $temp;
    }
	
} // end class
?>