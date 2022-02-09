<?php
/*		Freewheeling Easy Mapping Application
 *		A collection of routines for display of trail maps and amenities
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 * function loadBufferWithFile( $file )
 *      move contents of file to $buffer for later extraction
 *      throw error if file not avaiable or other problems
 *      returns number of characters loaded
 * function trimTo( $lookfor )
 *		edit $buffer and remove all characters befor 'lookfor'
 *		throw error if lookfor not found
 * function extractTo( $lookfor )
 *		returns from the start of buffer,upro, not including, lookfor		
 *		throw error if lookfor not found
 *		returns array($msgTemp, found string)
 * function removetags( $text )
 * 		removes atching tags from $text
 *		returns ($msg, cleaned out $text)
 * function findHref($text)
 *		returens an array of URLs associated with a href
 *		return ($msg, array of URLs);
 * fextractToEmptyLine( )
 *		===  rrwParse::extracr("\n\n"); 
 * function recursiveDirectoryIterator ($directory, $files
 *		return a list of all files under directory
 */
require_once "rrw_util_inc.php";
//require_once "simple_html_dom.php";

class rrwParse {
    static $buffer;
    static $debugParse= false;
    static $trace =  "";

    public static function recursiveDirectoryIterator( $directory = null, $files = array() ) {
        if ( !is_dir( $directory ) )
            throw new Exceptin( "'$directory' is not a directory. Now What?" );
		
        $iterator = new\ DirectoryIterator( $directory );
        foreach ( $iterator as $info ) {
            $filename = $info->__toString();
            if ( $info->isFile() ) {
                $files[ "$directory/$filename" ] = "$directory/$filename";
            } elseif ( $info->isDot() ) {
				continue;
			} elseif ($info->isDir() ) {
                $files = self:: recursiveDirectoryIterator("$directory/$filename", $files);
            } else {
				rrwUil::print_r($info, true, "a info is not a file, diretory or a dot");
			}
        }
        return $files;
    }
    public static function loadBufferWithFile( $file ) {
        SELF::$buffer = file_get_contents( $file );
        $sizeBuffer = strlen( SELF::$buffer );
        return "$sizeBuffer  haaract";
    }

    public static function trimTo( $lookfor ) {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;

        if (self::$debugParse) self::$trace .= " in trimTo buffer length is " . strlen( SELF::$buffer );
        if ( empty( SELF::$buffer ) )
           throw new Exception( "$msg $errorBeg E#913 buffer is empty $errorEnd");

        $iiLook = strpos( SELF::$buffer, $lookfor );
        if ( $iiLook === false )
            throw new Exception( "$msg $errorBeg E#913 did not finf $lookfor in buffer $errorEnd" );
        SELF::$buffer = substr( SELF::$buffer, $iiLook );
        return $msg;
    }
    public static function extractTo( $lookfor ) {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        $iiLook = strpos( SELF::$buffer, $lookfor );
        if ( $iiLook === false )
            throw new Exception( "$msg $errorBeg E#917 did not find $lookfor 
                    in remaiming buffer $errorEnd" );
        $extracted = substr( SELF::$buffer, 0, $iiLook );   // get the extraction
        $msg .= rrwParse::trimTo( $lookfor );       // and remove it from the buffer
        return array( $msg, $extracted );
    }

    public static function fextractToEmptyLine() {

        return rreParse::extractTo( "\n\n" );
    }

    public static function removetags( $text ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";

        $cnt = 0;
        $iiLookEnd = 0;
        while ( 1 ) {
            $cnt++;
            if ( $cnt > 50 )
                throw new Exception( "$msg $errorBeg E#786 to many tags found $errorEnd" . htmlspecialchars( $text ) . $eol );
            if ( $iiLookEnd > strlen( $text ) )
                break;
            $iiLookbeg = strpos( $text, "<", $iiLookEnd );
            if ( false === $iiLookbeg )
                break;
            $iiLookEnd = strpos( $text, ">", $iiLookbeg );
            if ( substr( $text, $iiLookbeg, 4 ) == "</p>" )
                continue; // leave </p> there
            if ( substr( $text, $iiLookbeg, 2 ) == "<p" ) {
                $iiLookbeg = $iiLookbeg + 2; // clen out <p .... >
                $iiLookEnd--;
            }

            $text = substr( $text, 0, $iiLookbeg ) . substr( $text, $iiLookEnd + 1 );
            $numremoved = $iiLookEnd - $iiLookbeg;
            $iiLookEnd = $iiLookEnd - $numremoved;
        }
        $text = trim( $text );
        if ( substr( $text, 0, 4 ) == "</p>" )
            $text = substr( $text, 5 ); // begins with </p>
        if ( substr( $text, -3 ) == "<p>" )
            $text = substr( $text, 0, strlen( $text ) - 3 ); // ends with <p>
        return array( $msg, $text );
    }
    private static function findHref( $text ) {

    }
} // end class
?>
