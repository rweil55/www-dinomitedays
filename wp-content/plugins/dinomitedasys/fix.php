<?php
	global $eol;
 
ini_set( "display_errors", true );
error_reporting( "E_ALL |E_STRICT)" );
$eol = "<br />\n";
    $dir = "/home/pillowan/www-dinomitedays";
print doit($dir);

function doit($dir) {
	global $eol;
    $msg = "";
    $handle = opendir( $dir );

    if ( !is_resource( $handle ) ) {
        return " directory $dir was not found ";
    }
    $msg .= "Directory handle: $dir --- $handle$eol";
    $msg .= "Entries:\n";
    /* This is the correct way to loop over the directory. */
    while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( substr( $entry, 0, 1 ) == "." || substr( $entry, 0, 2 ) == "wp" )
            continue;
        $msg .=  "$entry $eol \n";
    }
	return $msg;
} // end doit
print "fixed";

?>