<?php

require_once "display_tables_class.php";

class DisplayPhotographers {
    public static function Display( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $rrw_dinos, $rrw_photographers;
        $msg = ""; 

        $action = rrwUtil::fetchparameterString( "action" );
        $table = new rrwDisplayTable();
        $msg .= $table->tablename( $rrw_photographers );
        $msg .= $table->sortdefault( "photographer" );
        $msg .= $table->keyname( "photographer" );
        $msg .= $table->columns( "photographer Name", "photographer", 69 );
        $msg .= $table->columns( "E-Mail", "Address", 69 );
        $msg .= $table->columns( "Comment", "Comment", 69 );
        $msg .= $table->columns( "copyright Default", "copyrightDefault", 69 );

        if ( empty( $action ) ) {
            $msg .= $table->listdata();
            return $msg;
        }
        $msg .= $table->DoAction();
        return $msg;
    } // end function
} // end class
