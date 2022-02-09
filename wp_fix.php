<?php
global $eol;
global $level;

ini_set( "display_errors", true );
error_reporting( "E_ALL |E_STRICT)" );
$eol = "<br />\n";
$level = 1;
$dir = "/home/pillowan/www-dinomitedays";
print doit( $dir );

function doit( $dir ) {
    global $eol;
    global $level;
    $msg = "";
    $handle = opendir( $dir );

    if ( !is_resource( $handle ) ) {
        return " directory $dir was not found ";
    }
    $level++;
    $leadSpace = str_repeat( "&nbsp;", $level * 4 );
    $msg .= "$leadSpace Directory handle: $dir --- $handle$eol";
    $msg .= "$leadSpace Entries:$eol";
    /* This is the correct way to loop over the directory. */
    while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( substr( $entry, 0, 1 ) == "." || substr( $entry, 0, 2 ) == "wp" )
            continue;
        $thing = "$dir/$entry";
        if ( is_dir( $thing ) ) {
            $msg .= "$leadSpace$thing -- is a directory $eol";
            $msg .= doit( $thing );
            continue;
        }
        $msg .= "$leadSpace$entry $eol \n";
    }
	$level --;
    return $msg;
} // end doit
print "fixed";

?>