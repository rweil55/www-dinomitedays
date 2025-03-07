<?php

/**
 * Plugin Name: Archive.org Recover 
 * Plugin URI:  https://pluginserver.royweil.com/arcgiverecover/
 * Description: Checks your blog for broken links and missing images and notifies you on the dashboard if any are found.
 * Version: 1.1.7
 * Author:      Roy Weil
 * Author URI:  https://royweil.com/
 * Donate URI:  https://royweil.com/donate/
 * Text Domain: archive-recover
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
Archive.org Recover  is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Archive.org Recover  is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Archive.org Recover . If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

require_once "rrw_util_inc.php"; 
require_once "display_stuff_class.php";

$msg = rrw_archive_recover();
print $msg;
exit;

function rrw_archive_recover() {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $eol = "<br />\n";
    $errorBeg = "<span style='color:red'>";
    $errorEnd = "</span>$eol";
    ini_set( "display_errors", true );
    error_reporting( E_ALL | E_STRICT );

    $msg = "";
    $msg .= rrwFormat::elapsedTime( "" );
    $urlparselimit = rrwUtil::fetchParameterNumber( "urllimit" );
    $url = rrwUtil::fetchParameterString( "url" );
    $dire = rrwUtil::fetchParameterString( "dire" );
    $existingFiles = array();
    $FilestoParse = array();
    $parsedFiles = array();

    if ( empty( $urlparselimit ) ) {
        $url = "https://dinomitedays.org";
        $dire = "www-dinomitedays";
        $urlparselimit = "";
    }

    $msg .= "<form method='get' >
        <input type='text' size='100' name='url' value='$url' /> Base URL<br />
        <input type='text' size='100' name='dire' value='$dire' / Base Directory><br />
         <input type='text' name='urllimit' value = $urlparselimit> Use smaller number for testing &nbsp;
        <input type='submit' value='go recover' name='task' />
        <input type='submit' value='build jpg' name='task' />
       </form>
        ";
    if ( empty( $urlparselimit ) )
        return $msg;
    $task = rrwUtil::fetchParameterString("task");
    switch ($task ) {
        case "go recover":       
  //          $msg .= rrw_archive_recoverStart($url, $dire, $urlparselimit); 
            break;
        case "build jpg":
            $msg .= rrw_archive_displaystart($url, $dire, $urlparselimit); 
            break;
        default:
            $msg .= "Unknown task of '$task' $eol";
            break;
    } // end task switch
    return $msg;
}
function rrw_archive_displaystart($url, $dire, $urlparselimit) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $msg = "";
    $msg .= rrw_archive_getexisting( "/home/pillowan/$dire", $url );
    ksort($existingFiles);
    $msg .= "$eol $eol $eol ===================================================$eol";
    
    
    $countItems = 0;
    foreach ($existingFiles as $file=>$val) {
        $url = str_replace("/home/pillowan/www-dinomitedays/", 
                            "https://dinomitedays.org/", $file);
        $countItems++;
        $ext = substr($url, -3);
        switch( $ext) {
            case "jpg":
            case "gif":
            case "png":
            case "wav":
                $msg .= "<div height='250 width='200' ><img src='$url' ' \><br />$$countItems
                        " . htmlspecialchars($url) . "</div>\n";
                break;
            case "htm":
            case "tml":
                 $msg .= "<div height='250 width='200'><iframe src='$url'></iframe>
                 <br />$$countItems " . htmlspecialchars($url) . "</div>\n";
                break;
            default:
                $msg .= "<div height='250' width='200' > $errorBeg 
                        unkown task of $ext $url $errorEnd </div>";
                
        }   // end of  switch url
        $urlparselimit --;
        if ($urlparselimit < 0)
            break;
    }   // end of fo each
    return $msg;
}

function rrw_archive_recoverStart($url, $dire, $urlparselimit) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $msg .= "";
    $msg .= rrw_archive_getexisting( "/home/pillowan/$dire", $url );
    $counttries = 0;
    $msg .= "after find existing $dire found " . count( $existingFiles ) .
    "existing to parse " . count( $FilestoParse ) . $eol;

    while ( count( $FilestoParse ) > 0 ) {
        $msg .= "input parse limit $urlparselimit/$counttries $eol";
        $counttries++;
        if ( $counttries > 1000 ) {
            $msg .= "$errorBeg E#312 exit on tries $errorEnd";
            break;
        }
        $urlparselimit--;
        if ( $urlparselimit < 0 ) {
            $msg .= "$errorBeg E#312 exit on input parse limit $urlparselimit $errorEnd";
            break;
        }
        $msg .= rrw_archive_parseOnefile();
        $msg .= "There are " . count( $FilestoParse ) . " remang fles to process $eol";
    }
    $msg .= "to parse = " . count( $FilestoParse ) . ", existing = " . count( $existingFiles ) . $eol;
    $msg .= rrwUtil::print_r( $FilestoParse, true, "Files to parse" );
    $msg .= rrwFormat::elapsedTime( "" );
    return $msg;
}

function rrw_archive_getexisting( $dire, $url ) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    global $countparse;
    global $maxExisting;
    $msg = "";

    if ( !isset( $maxExisting ) )
        $maxExisting = 0;
    $maxExisting++;
    if ( $maxExisting > 100 ) {
        return "$errorBeg E#310 too many existing file $errorEnd";
    }
    $countFound = 0;
    $countparse = 0;
    $hd = opendir( $dire );
    while ( $filename = readdir( $hd ) ) {
        if ( "." == substr( $filename, 0, 1 ) )
            continue;
        if ( is_dir( "$dire/$filename" ) ) {
            $msg .= rrw_archive_getexisting( "$dire/$filename", "$url/$filename" );
            continue;
        }

        $existingFiles[ "$dire/$filename" ] = 1;
        $countFound++;
        $msg .= rrw_archive_pushToBeParsed( "$url/$filename" );
 //       $msg .= rrw_archive_Getfile( "$url/$filename" );
    } // end while
    $msg .= "$dire found $countFound/" . count( $existingFiles ) . ", to parse $countparse/";
    $msg .= count( $FilestoParse ) . $eol;
    return $msg;
}

function rrw_archive_pushToBeParsed( $url ) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    global $countparse;
    $msg = "";

    if ( (strpos( $url, "mailto:" ) !== false) || (strpos( $url, "@" ) !== false) )
        return "$msg ignoreing Mailto or #$eol";
    if (strpos( $url, "..") !== false) 
        return "$msg ignoreing /../ "; // code good be smarter, but for now
    $iislashslash = strrpos( $url, "//" );
    if ( $iislashslash === false || $iislashslash > 10 )
        return "$msg ignoring iislash = $iislashslash in $url $eol";
    if (array_key_exists($url, $parsedFiles) )
        return $msg;
    if (array_key_exists($url, $FilestoParse) )
        return $msg;

    $ext = substr( $url, -3 );
    switch ( strtolower( $ext ) ) {
        case "jpg":
        case "png":
        case "gif":
        case "wav":
            break;
        default:
            $FilestoParse[ $url ] = 1;
            $countparse++;
    } // end switch 
    return $msg;
}

function rrw_archive_parseOnefile() {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $msg = "";
    $debug = false;

    $url = array_key_first( $FilestoParse );
    unset( $FilestoParse[ $url ] );
    $msg .= "Parsing $url ... ";
    if ( !is_array( $parsedFiles ) )
        return "$msg Already been parsed $eol";
    if (strpos($url,".."))
        return "$msg ignore the /../ $eol";
    $msg .= rrw_archive_Getfile( $url );
    $msg .= $eol;
    $buffer = file_get_contents( $url );
    if ( $debug )$msg .= $buffer;
    $iislash = strrpos( $url, "/" );
    $base = substr( $url, 0, $iislash );
    foreach ( array( "img" => "src", "a" => "href" ) as $type => $lookfor ) {
        $iistart = 0;
        //       $msg .= "trying type $type, looking for $lookfor $eol";
        $msg .= searchbufferFor( $base, $buffer, $type, $lookfor );
    }
    $parsedFiles[ $url ] = 1;

    return $msg;
}

function searchbufferFor( $base, $buffer, $type, $lookfor ) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $alreadySearch = array();
    $msg = "";
    $debug = false;
    $urlCount = 0;
    $span = "";
    $iistart = 0;
    while ( true ) {
        $urlCount++;
        if ( 60 < $urlCount )
            break;
        list( $span, $iistart ) = getcompleteItenfromBuffer( $buffer, $type, $iistart );
        if ( $debug )$msg .= "found section of " . htmlspecialchars( $span ) . "at $iistart $eol";
        if ( empty( $span ) )
            break;
        // span is fom <a ... </a  or <img ... </img
        $iilook = strpos( $span, $lookfor );
        if ( $iilook === false and $type != "a") {
            $msg .= "$errorBeg E#305 $lookfor not found in -- " . htmlspecialchars( $span ) .
            $errorEnd;
            continue;
        } else {
            if (strpos($span, "name=")) {
                $msg .= "not href but name - ". htmlspecialchars( $span )  . "$eol";
                continue;
            }
        }
        $iichar = substr( $span, $iilook + strlen( $lookfor ) + 1, 1 );
        if ( '"' != $iichar && "'" != $iichar ) {
            $msg .= "$errorEnd #302 did not find a quote at $iistart - " .
            htmlspecialchars( $span ) . " $eol ";
            continue;
        }
        $iibeg = strpos( $span, $iichar, $iilook );
        $iiend = strpos( $span, $iichar, $iibeg + 1 );
        $item = substr( $span, $iibeg + 1, $iiend - $iibeg );
        if ( $debug )$msg .= "itemAddBaseDomain input  $item ";
        $item = itemAddBaseDomain( $base, $item );
        if ( $debug )$msg .= " -- final - $item $eol ";
        $msg .= rrw_archive_pushToBeParsed( $item );
        $msg .= rrw_archive_Getfile( $item);
    }
    return $msg;
}

function getcompleteItenfromBuffer( $buffer, $type, $iistart ) {
    global $eol, $errorBeg, $errorEnd;
    if ( $type == "img" )
        $debug = false;
    else
        $debug = false;

    if ( $debug ) print "search at $iistart for $type $eol";
    $iibegin = strpos( $buffer, "<$type ", $iistart );
    if ( $iibegin === false ) {
        if ( $debug ) print " &lt;$type not found $eol";
        return "";
    }
    $iiend = strpos( $buffer, "</$type", $iibegin );
    $iiend2 = strpos( $buffer, ">", $iibegin );
    if ( $debug ) print "start at $iibegin, " .
    htmlspecialchars( substr( $buffer, $iibegin, 160 ) ) .
    " iiend = $iiend, iiend2 = $iiend2 $eol";
    if ( $iiend !== false && $iiend2 !== false ) {
        if ( $iiend2 < $iiend )
            $iiend = $iiend2;
    }
    if ( $iiend === false )
        $iiend = $iiend2;
    if ( $iiend === false ) {
        return "";
    }
    if ( $debug ) print "extracting $iibegin to $iiend $eol";

    $item = substr( $buffer, $iibegin, $iiend - $iibegin );
    return array( $item, $iiend );
}

function itemAddBaseDomain( $base, $item ) {
    global $eol, $errorBeg, $errorEnd;

    $item = substr( $item, 0, strlen( $item ) - 1 ); // remove trail quote
    if ( strpos( $item, "web.archive.org" ) !== false ) {
        $iistart = strpos( $item, "web" );
        $iistart = strpos( $item, "http", $iistart );
        $item = substr( $item, $iistart );
        $item = str_replace( "index_files", "graphics", $item );
        return $item;
    }
    if ( substr( $item, 0, 2 ) == "./" ) {
        $item = $base . substr( $item, 1 );
        $item = substr( $item, 0, strlen( $item ) - 1 );
        return $item;
    }
    return "$base/$item";
}


function rrw_archive_Getfile( $url ) {
    global $eol, $errorBeg, $errorEnd;
    global $existingFiles, $FilestoParse, $parsedFiles;
    $msg = "";
    $debug = false;

    $msg .= "Getting url <a href='$url ' target='next'>$url</a>";
    $urlNohttp = str_replace( "http://", "", $url );
    $urlNohttp = str_replace( "https://", "", $urlNohttp );
    $urlNohttp = str_replace( "www.", "", $urlNohttp );
    if ( $debug )$msg .= "urlNohttp $urlNohttp $eol";
    $size = 16; //12345678901234567
    if ( strncmp( "dinomitedays.org", $urlNohttp, 16 ) == 0 ) {
        $filename = "/home/pillowan/www-dinomitedays" . substr( $urlNohttp, $size );
        if ( $debug )$msg .= "File $filename $eol";
        if ( array_key_exists( $filename, $existingFiles ) ) {
            $msg .= "File already recovered $eol";
            return $msg;
        }
    } else {
        $msg .= "$errorBeg E#309 tried to get a non dinomitedays file $urlNohttp $errorEnd";
        return $msg;
    }
    $source = "https://archive.org/wayback/available?url=$urlNohttp";
    $fileContents = file_get_contents( $source );
    if ( $fileContents === false )
        $msg .= "readfle failure - $source ";
    if ( $debug )$msg .= "json =  $fileContents $eol";
    $arcData = json_decode( $fileContents, true );
    if ( 0 == count( $arcData[ "archived_snapshots" ] ) ) {
        $msg .= "$errorBeg #301 has not been saved in archive.org == 
                    $fileContents  $errorEnd";
        return $msg;
    }
    //  if ($debug) $msg.= rrwUtil::print_r($arcData, true, 'Jaon output ');
    $url = $arcData[ "archived_snapshots" ][ "closest" ][ "url" ];
    $slash = strrpos( $url, "/htt" );
    $url = substr( $url, 0, $slash ) . "id_" . substr( $url, $slash );
    if ( $debug )$msg .= "Url = $url $eol ";

    $pageContents = @file_get_contents( $url );

    $msg .= rrw_archive_write( $pageContents, $filename, $url );
    return $msg;
}

function rrw_archive_write( $pageContents, $filename, $url ) {
   global $eol, $errorBeg, $errorEnd;
    $msg = "";
     if ( $pageContents === false )
        return "$errorBeg #e307 File '$url' was not read $errorEnd ";
    if ( empty( $filename ) )
        return "$errorBeg E#315 Output file not specifed $errorEnd";
    $fp = @fopen( $filename, "w" );
    if ( is_resource( $fp ) ) {
        fwrite( $fp, $pageContents );
        fclose( $fp );
        $existingFiles[ $filename ] = 1;
        return "file <a href='$url' target='next' > $filename</a> updated $eol ";
    }
    if ( (strpos( $filename, "index.htm" ) === false)  &&  
        (strpos( $filename, "index.htm" ) === false)  )
        return "$errorBeg E#319 file not tot open for write, not index.htm $errorEnd";
    $direname = substr( $filename, 0, strpos( $filename, "index.htm" ) );
    $msg .= "$errorBeg E#306 makng dire $direname $errorEnd";
    mkdir( $direname );
    $fp = fopen( $filename, "w" );
    if ( is_resource( $fp ) ) {
        fwrite( $fp, $pageContents );
        fclose( $fp );
        $existingFiles[ $filename ] = 1;
        return "file <a href='$url' target='next' > $filename</a> updated $eol ";
    } else
        return "$errorBeg E#308 after mkdir $direname 
                                File '$filename' did not open for writting $errorEnd ";
    return "$errorBeg E#318 can not get here $errorEnd";
}

//add_shortcode( "rrw_archive_recover", "rrw_archive_recover" );
