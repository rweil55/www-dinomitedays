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
	global $eol;
    $eol = "<br />\n";
    $msg = "";

	$msg .= doit();
    print $msg;
} catch ( Exception $ex ) {
    print "$msg E#302 Got an e r r o r - " . $ex->getMessage();
}
exit();


function doit() {
	global $eol;
	$msg = "";
	$debug = true;

    $options = "";
    switch ( 4 ) {
        case 1:
            $dir = "/home/pillowan/www-dinomitedays/designs";
            $fix = "find_filename";
            break;
        case 2:
            $dir = "/home/pillowan/www-dinomitedays";
            $fix = "http2https";
            break;
        case 3:

            $dir = "/home/pillowan/www-dinomitedays";
            $fix = "dinomitedaysauction";
            break;
        case 4:
            $dir = "/home/pillowan/www-dinomitedays";
            $fix = "drivingtour";
			break;
		case 5:
			$msg .= setLocation( "phillips.htm", "4501 Forbes AVe", "2022", "40.44435,-79.9498" );
    		//	$msg .= applyURLfixrs();
			break;
			
    }


    $dino = rrwUtil::fetchParameterString( "dino" );
    if ( !empty( $dino ) ) {
        $dir = "/home/pillowan/www-dinomitedays";
        $fix = "find_images";
        $options = $dino;
    }

    if ( $debug ) $msg .= "Start $fix - on - $dir $eol";

    $msg .= doFixLoop( $fix, $dir, $options );
	return $msg;
}

function doFixLoop( $fix, $dir, $options ) {
 	global $eol;
    $msg = "doFixLoop on directory $dir $eol";
    $debugWhile = false;

    $iiDays = strpos( $dir, "days" );
    if ( $debugWhile )$msg .= "<h1> fix stuff $fix  - $dir</h1>";

    $http = "https://dinomitedays.org" . substr( $dir, $iiDays + 4 );

    $handle = opendir( "$dir" );
    if ( !is_resource( $handle ) )
        throw new Exception( "$msg E#301 that is not a directory" );
    $cnt = 0;
    $entry = true;
    $msg .= "<ul class='rrwPhotoGrid' role='list'>
        ";
    while ( ( $entry = readdir( $handle ) ) !== false ) {
        $cnt++;
        if ( $cnt > 2000 )
            throw new Exception( "$msg E#303 - $entry Too mnay times $cnt in the while loop $eol" );
        if ( ( "." == substr( $entry, 0, 1 ) ) || ( "fix" == $entry ) || ( "wp" == substr( $entry, 0, 2 ) ) )
            continue;
        $file = "$dir/$entry";
        if ( $debugWhile )$msg .= "$file $eol";
        if ( is_dir( $file ) ) {
            $msg .= doFixLoop( $fix, "$dir/$entry", $options );
            continue;
        }
        $buffer = file_get_contents( $file );
        $originalLength = strlen( $buffer );
        switch ( $fix ) {
            case "dinomitedaysauction":
                $buffer = str_replace( 'https://carnegiemnh"', 'https://carnegiemnh.org"', $buffer );
                break;
            case "http2https":
                $ext = substr( $entry, -3 );
                if ( "htm" != $ext )
                    continue;
                $buffer = file_get_contents( $file );
                $len1 = strlen( $buffer );

                $buffer = str_replace( "dinomitedaysauction", "dinomitedays/auction", $buffer );
                $buffer = str_replace( "dinomiteday/sauction", "dinomitedays/auction", $buffer );
                $buffer = str_replace( "www.CarnegieMNH", "carnegiemnh", $buffer );
                $buffer = str_replace( "http://carnegiemuseums", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "http://www.carnegiemuseums", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "http://carnegiemnh", "https://carnegiemnh", $buffer );
                //         $buffer = str_replace( "https://.carnegiemnh", "https://carnegiemnh", $buffer );
                $buffer = str_replace( "https://carnegiemnh/", "https://carnegiemnh.org", $buffer );
                //         $buffer = str_replace( "https://carnegiemnh.org/cmnh", "https://carnegiemnh.org", $buffer );
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
                if ( ( "gif" != $ext ) && ( "jpg" != $ext ) )
                    continue;
                if ( strpos( $file, $options ) === false )
                    continue;
                $msg .= "<li><img src='$http/$entry' width='200px' /> <br>$http/$entry</li>";
                break;
			case "drivingtour":
				$ext = substr($file, -3);
				if ("htm" != $ext)
					break;
				if (false !== strpos($buffer, "tour was")) {
				$iiSlash = strrpos($file, "/");
				$link = "https://dinomitedays.org/" . substr($file, $iiSlash);
				$msg .= "check [ <a href='$link' target='new' >$file</a> ] $eol";
					break;
				}
				$iiLoc = strpos($buffer, "locations of the"); 
				if (false === $iiLoc)
					break;
				$iidino = strpos($buffer, "dino", $iiLoc);
				if (false === $iidino)
					break;
				$diff = $iidino - $iiLoc;;
				$msg .= "Diff = $diff  &nbsp; $file $eol";
				if (68 != $diff)
					break;
				$tour = "<a href='https://carnegiemnh.org/jurassic-days-dino-statue-driving-tour/' 
					> a tour was created. </a>";
				$step =10;
				$buffer = substr($buffer, 0, $iidino + $step) . 
					"However some of the dinosaurs were
					located in 2010, and $tour " . substr($buffer, $iidino + $step);
				$newFile = "${file}l";
	//			$fp = fopen ($newFile, "w");
//				fwrite ($fp, $buffer);
//				fclose($fp);
				$iiSlash = strrpos($file, "/");
				$link = "https://dinomitedays.org/" . substr($file, $iiSlash);
				$msg .= "[ <a href='$link' target='new' >$file</a> ] ";
				$iiSlash =  strrpos($newFile, "/");
				$link = "https://dinomitedays.org/" . substr($newFile, $iiSlash);
				$msg .= "[ <a href='$link' target='new' >$newFile</a> ] $eol";
	            break;
            default:
                print "no fix selected ";
                break;
        } // end switch
        $fianlLength = strlen( $buffer );
        if ( $originalLength != $fianlLength ) {
            $msg .= "$file length changed $originalLength != $fianlLength $eol";
            $fp = fopen( $file, "w" );
            $cntWriten = fwrite( $fp, $buffer );
            fclose( $fp );
            $msg .= "Write $cntWriten bytes of information $eol";
        }
    } // end while
    $msg .= "</ul>\n fix loop finished $eol";
    return $msg;
} // end function
	
function setLocation( $filename, $address, $year, $latLong ) {
    global $eol, $errorBeg, $errorEnd;
    $msg = "";

    $fileLoc = "/home/pillowan/www-dinomitedays/designs/$filename";
    $isGood = rrwUtil::fetchparameterBoolean( "isgood" );
    if ( $isGood )
        $fileLocOut = $fileLoc;
    else
        $fileLocOut = $fileLoc . "l";
    $buffer = file_get_contents( $fileLoc );

    $iiloc = strpos( $buffer, "Location" );
    if ( false === $iiloc )
        return "$msg $errorBeg E#800 the word location was not found. $errorEnd";
    $iiloc = $iiloc + 8; // iiloc is hust after the N
    $msg .= "'" . substr( $buffer, $iiloc, 2 ) . "' $eol";
    if ( substr( $buffer, $iiloc, 2 ) == " (" )
        $iiloc2 = $iiloc + 7;
    else
        $iiloc2 = $iiloc;
    if ( !empty( $year ) )
        $buffer = substr( $buffer, 0, $iiloc ) . " ($year)" . substr( $buffer, $iiloc2 );

    $iiloc = strpos( $buffer, "</font", $iiloc2 ) + 7;
    $iiloc2 = strpos( $buffer, "<br>", $iiloc );
    if ( !empty( $address ) ) {
        $buffer = substr( $buffer, 0, $iiloc ) . " $address" . substr( $buffer, $iiloc2 );
        $iiloc += strlen( $address ) + 1;
    }
    if ( !empty( $latLong ) ) {
        $link = "<a href='https://www.google.com/maps/place/$latLong' target='new'>$latLong</a>";
        $buffer = substr( $buffer, 0, $iiloc ) . " $link" . substr( $buffer, $iiloc );
    }

    $fp = fopen( $fileLocOut, "w" );
    fwrite( $fp, $buffer );
    $iiSlash = strrpos( $fileLocOut, "/" );
    $newName = substr( $fileLocOut, $iiSlash );
    $msg .= "<a href='https://www.dinomitedays.org/designs/${newName}' 
			target='new' > $newName </a>";

    return $msg;
}
	
?>
</body>
</html>