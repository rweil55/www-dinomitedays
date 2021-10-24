<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fix something #2</title>
</head>

<body>
<?php

ini_set( "display_errors", true );
error_reporting( E_ALL | E_STRICT );

require_once "rrw_util_inc.php";
try {
    $eol = "<br />\n";
    $msg = "";
    $debug = true;

    $options = "";
    $dir = "/home/pillowan/www-dinomitedays/designs";
    $fix = "find_filename";

    $dir = "/home/pillowan/www-dinomitedays";
    $fix = "remove dipNN";

    $dino = rrwUtil::fetchParameterString( "dino" );
    if ( !empty( $dino ) ) {
        $dir = "/home/pillowan/www-dinomitedays";
        $fix = "find_images";
        $options = $dino;
    }

    if ( $debug ) print "Start $fix - on - $dir $eol";

    $msg .= doFixLoop( $fix, $dir, $options );
    print $msg;
} catch ( Exception $ex ) {
    print "$msg E#302 Got an e r r o r - " . $ex->getMessage();
}
exit();

function doFixLoop( $fix, $dir, $options ) {
    $eol = "<br />\n";
    $msg = "";
    $debugWhile = false;
    
    $iiDays = strpos($dir, "days");
    if ($debugWhile) $msg .= "<h1> fix stuff $fix  - $dir</h1>";
    
    $http = "https://dinomitedays.org" . substr($dir, $iiDays + 4);

    $handle = opendir( "$dir" );
    if ( !is_resource( $handle ) )
        throw new Exception( "$msg E#301 that is not a directory" );
    $cnt = 0;
    $entry = true;
      $msg .= "<ul class='rrwPhotoGrid' role='list'>
        ";
    while ( ( $entry = readdir( $handle ) ) !== false ) {
        $cnt++;
        if ( $cnt > 1500 )
            throw new Exception( "$msg E#303 - $entry Too mnay times $cnt in the while loop $eol" );
        if ( ( "." == substr( $entry, 0, 1 ) ) || ( "fix" == $entry ) || ( "wp" == substr( $entry, 0, 2 ) ) )
            continue;
        $file = "$dir/$entry";
        if ($debugWhile) $msg .= "$file $eol";
        if ( is_dir( $file ) ) {
            $msg .= doFixLoop( $fix, "$dir/$entry", $options );
            continue;
        }

        switch ( $fix ) {
            case "remove dipNN":
                $ext = substr( $entry, -3 );
                if ( "htm" != $ext )
                    continue;
                $buffer = file_get_contents( $file );
                $len1 = strlen( $buffer );
                $buffer = str_replace( "www.CarnegieMNH", "carnegiemnh", $buffer );
                $buffer = str_replace( "http://carnegiemuseums", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "http://www.carnegiemuseums", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "http://carnegiemnh", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "https://.carnegiemnh", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "https://carnegiemnh/", "https://carnegiemnh.org", $buffer );
                $buffer = str_replace( "https://carnegiemnh.org/cmnh", "https://carnegiemnh.org", $buffer );
                $buffer = str_replace( "https://carnegiemnh.org/index.htm", "https://carnegiemnh.org", $buffer );
                $buffer = str_replace( "https://carnegiemuseums.org/cmnh", "https://carnegiemnh", $buffer );
                $len2 = strlen( $buffer );
                //            $msg .= "$len1, $len2, $file $eol";
                if ( $len1 != $len2 ) {
                    $fp = fopen( $file, "w" );
                    fwrite( $fp, $buffer );
                    fclose( $fp );
                    $msg .= "$cnt - updated file $file$eol";
                }
                break;
            case "find_filename":
                 $ext = substr( $entry, -3 );
                if ( "htm" != $ext )
                    continue;
                $buffer = file_get_contents( $file );
                $len1 = strlen( $buffer );
                $iidip = strpos( $buffer, "src=\"graphics/dip" );
                if ( $iidip !== false ) {
                    $number = ( int )substr( $buffer, $iidip + 17, 2 );
                    $dinoname = substr( $entry, 0, -4 );
                    $msg .= "update `dinos` set Filename = '$dinoname' where keyid = $number ; $eol";
                }
                break;
            case "find_images":
                $ext = substr( $entry, -3 );
 //               $msg .=  "$entry - $ext $eol";
               if ( ("gif" != $ext) && ("jpg" != $ext) )
                    continue;
                if ( strpos( $file, $options ) === false )
                    continue;
                $msg .= "<li><img src='$http/$entry' width='200px' /> <br>$http/$entry</li>";
                break;
            default:
                print "no fix selected ";
                break;
        } // end switch
    } // end while
    $msg .= "</ul>\n";
    return $msg;
} // end function
?>
</body>
</html>