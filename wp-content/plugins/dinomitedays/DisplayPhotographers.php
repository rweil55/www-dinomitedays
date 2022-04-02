<?php

class DisplayPhotographers {
    public static function Display( $attr ) {
        global $eol, $errorBeg, $errorEnd;
 global $wpdbExtra, $rrw_dinos, $rrw_photographers;
       $msg = "";
 
        $action = rrwUtil::fetchparameterString( "action" );
        tablename( $rrw_photographers );
        sortdefault( "photographer" );
        seqname( "photographer" );
        columns( "photographer Name", "photographer", 69 );
        columns( "E-Mail", "Address", 69 );
        columns( "Comment", "Comment", 69 );
        columns( "copyright Default", "copyrightDefault", 69 );
        
        if ( empty( $action ) ) {
            $msg .= listdata();
            return $msg;
        }
        $msg .= DoAction();
        return $msg;
    }   // end function
} // end class
?>
