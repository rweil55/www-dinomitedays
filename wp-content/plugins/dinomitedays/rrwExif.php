G<?php

ini_set( "display_errors", true );
error_reporting( E_ALL | E_STRICT );
use lsolesen\ pel\ Pel;
use lsolesen\ pel\ PelConvert;
use lsolesen\ pel\ PelCanonMakerNotes;
use lsolesen\ pel\ PelDataWindow;
use lsolesen\ pel\ PelEntryException;
use lsolesen\ pel\ PelEntryAscii;
use lsolesen\ pel\ PelEntryByte;
use lsolesen\ pel\ PelEntryCopyright;
use lsolesen\ pel\ PelEntryLong;
use lsolesen\ pel\ PelEntryNumber;
use lsolesen\ pel\ PelEntryRational;
use lsolesen\ pel\ PelEntryShort;
use lsolesen\ pel\ PelEntrySShort;
use lsolesen\ pel\ PelEntrySRational;
use lsolesen\ pel\ PelEntrySLong;
use lsolesen\ pel\ PelEntryTime;
//use lsolesen\pel\PelEntryUndefined;
use lsolesen\ pel\ PelEntryUserComment;
use lsolesen\ pel\ PelEntryUserCopyright;
use lsolesen\ pel\ PelEntryVersion;
use lsolesen\ pel\ PelEntryWindowsString;
use lsolesen\ pel\ PelEntryUndefined;
use lsolesen\ pel\ PelExif;
use lsolesen\ pel\ PelFormat;
use lsolesen\ pel\ PelIfd;
use lsolesen\ pel\ PelIfdException;
use lsolesen\ pel\ PelIllegalFormatException;
use lsolesen\ pel\ PelInvalidDataException;
use lsolesen\ pel\ PelJpeg;
use lsolesen\ pel\ PelJpegComment;
use lsolesen\ pel\ PelJpegContent;
use lsolesen\ pel\ PelJpegInvalidMarkerException;
use lsolesen\ pel\ PelJpegMarker;
use lsolesen\ pel\ PelMakerNotes;
use lsolesen\ pel\ PelTag;
use lsolesen\ pel\ PelTiff;
use lsolesen\ pel\ PelWrongComponentCountException;
require_once "pel-h.php";

class rrwExif {
   
    function readexifItem( $filename, $item, & $msg ) {
        global $eol, $errorBeg, $errorEnd;
        ini_set( "display_errors", true );
        error_reporting( E_ALL | E_STRICT );
        if ( !file_exists( $filename ) ) {
            $tr = rrwFormat::backtrace( 5 );
            throw new Exception( "$msg $errorBeg E#431 readexifItem: $filename 
                    does not exists $errorEnd $tr $eol" );
        }
        if ( filesize( $filename > 10000 ) )
            throw new Exception( "$msg $errorBeg E#432 readexifItem: $filename 
                    is to big " . round( filesize( $filename ) / 1024, 0 ) . " $errorEnd" );
        $exif = self::rrw_exif_read_data( $filename );
        if ( array_key_exists( $item, $exif ) )
            return $exif[ $tem ];
        return "&lt;Missing&gt;";
    }

    public static function pushToImage( $photoname, $item, $value ) {
        // insert the item/value into filename
        global $eol, $errorBeg, $errorEnd;
        global $photoPath;
        $msg = "";
        $debugExif = rrwUtil::setDebug( "debugexif" );
        $debugExifDumpMeta = rrwUtil::setDebug( "debugexifdumpmeta" );
        if ( false === strpos( $photoname, "home" ) )
            $filename = "$photoPath/$photoname" . "_cr.jpg";
        else
            $filename = $photoname;
        $tmpfname = str_replace( "jpg", "_copyright.jpg", $filename );
        if ( $debugExif )$msg .= "filename $filename $eol tempname $tmpfname $eol";
        if ( !file_exists( $filename ) )
            throw new Exception( "$msg $errorBeg E#741 file $filename does not exist $errorEnd", -741 );
        if ( $debugExif )$msg .= "changeItem( $filename, $tmpfname, $item, $value) $eol";
        $msg .= self::changeItem( $filename, $tmpfname, $item, $value );
        if ( !file_exists( $tmpfname ) ) {
            sleep( 1 );
            if ( !file_exists( $tmpfname ) )
                throw new Exception( "$msg $errorBeg E#745 temp file not there 
                                    $errorEnd" );
        };
        $sizeOld = filesize( $filename );
        $sizeNew = filesize( $tmpfname );
        if ( $debugExif || $debugExifDumpMeta )$msg .= rrwExif::dumpMeta( $filename, $tmpfname );
        if ( abs( $sizeOld - $sizeNew ) > 500 ) {
            $diff = $sizeNew - $sizeOld;
            $msg .= "$errorBeg E#748 old size is $sizeOld, new size is $sizeNew,
                    difference  ($diff) 
                    is more than 500 please check, perhaps thumb nail changed size $errorEnd";
            $msg .= rrwExif::dumpMeta( $filename, $tmpfname );
        }

        unlink( $filename );
        rename( $tmpfname, $filename );
        if ( $debugExif )$msg .= "rename( $tmpfname, $filename ) $eol";
        return $msg;
    }
    /*
            global $eol, $errorBeg, $errorEnd;
            global $testPath;
            $msg = "";
            $debugExif = false;
            try {
                if ( $debugExif )$msg .= "---------------------------------------$eol";
                if ( false === strpos( $filename, "home" ) )
                    $filename = "$testPath/$filename" . "_cr.jpg";
                //       ini_set( 'memory_limit', '32M' );
                if ( $debugExif )$msg .= "( $filename, $item, $value ) $eol";
                $tmpfname = str_replace( "jpg", "_copyright.jpg", $filename );
                if ( $debugExif )$msg .= "tempfile is $tmpfname $eol";
                $contents = file_get_contents( $filename );
                if ( $debugExif )$msg .= " got contents of $filename $eol";
                $ss = strlen( $contents );
                if ( $debugExif )$msg .= " contents size is $ss $eol";
                $data = new PelDataWindow( $contents );
                if ( $debugExif )$msg .= " new PelDataWindow worked  $eol";
                $jpeg = $fileInMemory = new PelJpeg();
                if ( $debugExif )$msg .= " new PelJpeg() worked  $eol";
                try {
                    //          $jpeg->load( $data );
                    $jpeg->loadfile( $filename );
                } // end try
                catch ( Exception $ex ) {
                    throw new Exception( "$errorBeg E#703 working on $item, 
                        jprg->load(data) failed" . $ex->getMessage() . $errorEnd );
                }
                if ( $debugExif )$msg .= "load worked  $eol";
                $pelExif = $jpeg->getExif(); // get the desired data
                if ( $debugExif )$msg .= "pelExif is loaded $eol";
                if ( $pelExif == null ) {
                    if ( $debugExif )$msg .= self::println( 'No exif found, create new.' );
                    $pelExif = new PelExif(); // create on
                    $jpeg->setExif( $pelExif ); // insert it into the memory version
                }
                $pelTiff = $pelExif->getTiff();
                if ( $pelTiff == null ) {
                    if ( $debugExif )$msg .= self::println( 'No Tiff found, create new.' );
                    $pelTiff = new PelTiff();
                    $pelExif->setTiff( $pelTiff );
                }

                $pelIfd0 = $pelTiff->getIfd();
                if ( $pelIfd0 == null ) {
                    if ( $debugExif )$msg .= self::println( 'No ifdo found, create new.' );
                    $pelIfd0 = new PelIfd( PelIfd::IFD0 );
                    $pelTiff->setIfd( $pelIfd0 );
                }

                if ( $debugExif )$msg .= "compleated setup, get/adjust the data $eol ";
                $tag = self::convertText2EeixID( $item ); // item name into tag integer
                if ( $debugExif )$msg .= "item is $item, tag integer is $tag (" .
                dechex( $tag ) . ") $eol ";
                $textThing = $pelIfd0->getEntry( $tag );
                if ( is_null( $textThing ) ) {
                    if ( $debugExif )$msg .= "tag did not exist, create a new one $eol";
                    $type = self::findTagtype( $tag );
                    if ( $debugExif )$msg .= "Adding new $tag (" . dechex( $tag ) . ") of type $type  with value $value";
                    switch ( $type ) {
                        case "Copyright":
                        case "copyright":
                            $textThing = new PelEntryCopyright( $value );
                            break;
                        case "Ascii":
                        case "ascii":
                            $textThing = new PelEntryAscii( $tag, $value );
                            break;
                        case "byte":
                        case "Byte":
                            $textThing = new PelEntryByte( $tag, $value );
                            break;
                        default:
                            throw new Exception( "E#488 Unknown self::findTagtype for $tag" );
                            break;
                    }
                    $pelIfd0->addEntry( $textThing );
                    $textThing = $pelIfd0->getEntry( $tag );
                    $oldValue = "";
                } else {
                    // update an existing tag
                    if ( $debugExif )$msg .= "tag thing exits $eol";
                    $oldValue = $textThing->getValue();
                    if ( $debugExif )$msg .= rrwUtil::print_r( $oldValue, true, "found old value of $item" );
                    $textThing->setValue( $value );
                }
                $newValue = $textThing->getValue();
                if ( $debugExif )$msg .= rrwUtil::print_r( $newValue, true, " set new tag value of $item" );
                if ( $debugExif )$msg .= self::println( "Writing file $tmpfname $eol" );
                $fileInMemory->saveFile( $tmpfname );
                $jpeg->saveFile( $tmpfname );
                $sizeOld = filesize( $filename );
                $sizeNew = filesize( $tmpfname );
                if ( $debugExif )$msg .= rrwExif::dumpMeta( $filename, $tmpfname );
                if ( abs( $sizeOld - $sizeNew ) < 500 ) {
                    //      copy($tmpfname, "$filename.jpg");
                    //    if ( $debugExif )$msg .= "copy( $tmpfname, $filename.jpg ) $eol";
                    //   return $msg;
                    unlink( $filename );
                    rename( $tmpfname, $filename );
                    if ( $debugExif )$msg .= "rename( $tmpfname, $filename ) $eol";
                } else {
                    if ( true ) {
                        unlink( $filename );
                        rename( $tmpfname, $filename );
                        if ( $debugExif )$msg .= "rename( $tmpfname, $filename ) $eol";
                    }
                    $err = " old size is $sizeOld, new size is $sizeNew,
                    difference  (" . $sizenew - $sizeold . ") 
                    is more than 500 please check$eol";
                    throw new Exception( "$msg E#444 $err" );
                }
                $ii = strrpos( $filename, "/" );
                $basename = substr( $filename, $ii );
                $itemname = str_replace( ".jpg", "", $basename );
                $itemname = str_replace( "_cr", "", $itemname );
                $itemname = str_replace( "_tmb", "", $itemname );
                // copyright and comment may be stored as an arrray

                if ( is_array( $newValue ) )
                    $newDisplay = rrwUtil::print_r( $newValue, true, "array" );
                else
                    $nameDisplay = $newValue;
                $comment = "$basename -- $oldValue -> $newDisplay";
                $msg .= rrwUtil::InsertIntoHistory( $itemname, $comment );
                if ( $debugExif )$msg .= "History writin $comment $eol ----- $eol";
            } // end try
            catch ( Exception $ex ) {
                $msg .= "$errorBeg E#963 in pushtoimage: " . $ex->getMessage() . $errorEnd;
            }
            return $msg;
        }
    */
    function testperl() {
        global $testPath, $eol;
        ini_set( 'display_errors', 1 );
        ini_set( 'display_startup_errors', 1 );
        error_reporting( E_ALL );
        $msg = "";
        /** Create an object */
        $filename = "$testPath/input.jpg";
        $last_line = system( 'ls', $retval );
        $msg .= "$eol last line $last_line $eol";
        $exifLastLine = system( "Exif/exiftool -s $filename" );
        $msg .= "$eol exif last line -- $exifLastLine  $eol";
        $msg .= "$eol perhaps";
        return $msg;
    }

    private static function writeoutput() {
        ini_set( "display_errors", true );
        global $testPath, $eol;
        $msg = "";
        /** Create an object */
        $filename = "$testPath/input.jpg";
        $filenameNew = "$testPath/output.jpg";
        if ( file_exists( $filenameNew ) )
            unlink( $filenameNew );
        $msg .= "file unlinked $eol";
        $er = new phpExifWriter( $filename );
        $msg .= "phpExifWriter( $filename ); involed $eol";
        $er->debug = true;
        /*
         * Add comments to the file
         */
        $er->addComment( "This is the commentss" );
        $msg .= "phpExifWriter( comment added $eol";
        /**
         * Write back the content to a file
         */
        $er->writeImage( $filenameNew );
        $msg .= "file written $eol";
        $msg .= readinputa4( $attr );
        return $msg;
    }

    /* a printf() variant that appends a newline to the output. */
    private static function println( $fmt, $value = "" ) {
        global $eol, $errorBeg, $errorEnd;
        if ( !empty( $value ) )
            $fmt = sprintf( $fmt, $value );
        return "$fmt $eol";
    }

    private static function rrwPHPel( $argv ) {
        /*
         * Store the name of the script in $prog and remove this first part of
         * the command line.
         */
        global $eol, $errorBeg, $errorEnd;
        ini_set( 'display_errors', 1 );
        ini_set( 'display_startup_errors', 1 );
        error_reporting( E_ALL | E_STRICT );
        $msg = "";
        $debug = rrwUtil::setDebug( "debugrrwphpel" );
        try {
            if ( $debug )$msg .= self::println( "--------------------- emter self::rrwPHPel $eol" );
            $prog = array_shift( $argv );
            $error = false;
            /*
             * The next argument could be -d to signal debug mode where lots of
             * extra information is printed out when the image is parsed.
             */
            if ( isset( $argv[ 0 ] ) && $argv[ 0 ] == '-d' ) {
                Pel::setDebug( true );
                array_shift( $argv );
            }
            /* The mandatory input filename. */
            if ( isset( $argv[ 0 ] ) ) {
                $input = array_shift( $argv );
            } else {
                $error = true;
            }
            /* The mandatory output filename. */
            if ( isset( $argv[ 0 ] ) ) {
                $output = array_shift( $argv );
            } else {
                $error = true;
            }
            /* The mandatory item to be updated filename. */
            if ( isset( $argv[ 0 ] ) ) {
                $tag = array_shift( $argv );
            } else {
                $error = true;
            }
            /*
             * Usage information is printed if an error was found in the command
             * line arguments.
             */
            if ( $error ) {
                if ( $debug )$msg .= self::println( 'Usage: %s [-d] <input> <output> [desc]', $prog );
                if ( $debug )$msg .= self::println( 'Optional arguments:' );
                if ( $debug )$msg .= self::println( '  -d    turn debug output on.' );
                if ( $debug )$msg .= self::println( '  desc  the new description.' );
                if ( $debug )$msg .= self::println( 'Mandatory arguments:' );
                if ( $debug )$msg .= self::println( '  input   the input file, a JPEG or TIFF image.' );
                if ( $debug )$msg .= self::println( '  output  the output file for the changed image.' );
                return $msg;
            }
            /* Any remaining arguments are considered the new description. */
            $newValue = implode( ' ', $argv );
            if ( $debug )$msg .= "inputfile - $input, $eol outputfile $output, $eol
              item = $tag (" . dechex( $tag ) . "), new value - $newValue $eol";

            /*
             * We typically need lots of RAM to parse TIFF images since they tend
             * to be big and uncompressed.         */
            ini_set( 'memory_limit', '120M' );
            /*
             * The input file is now read into a PelDataWindow object. At this
             * point we do not know if the file stores JPEG or TIFF data, so
             * instead of using one of the loadFile methods on PelJpeg or PelTiff
             * we store the data in a PelDataWindow.
             */
            if ( $debug )$msg .= "Reading file $input ...$eol";
            $buffer = file_get_contents( $input );
            if ( $debug )$msg .= self::println( "got " . strlen( $buffer ) . "bytes from the files $eol" );

            $data = new PelDataWindow( $buffer );
            if ( $debug )$msg .= "got data $eol";

            /*
             * The static isValid methods in PelJpeg and PelTiff will tell us in
             * an efficient maner which kind of data we are dealing with.
             */
            if ( PelJpeg::isValid( $data ) ) {
                /*
                 * The data was recognized as JPEG data, so we create a new empty
                 * PelJpeg object which will hold it. When we want to save the
                 * image again, we need to know which object to same (using the
                 * getBytes method), so we store $jpeg as $file too.
                 */
                $jpeg = $file = new PelJpeg();
                /*
                 * We then load the data from the PelDataWindow into our PelJpeg
                 * object. No copying of data will be done, the PelJpeg object will
                 * simply remember that it is to ask the PelDataWindow for data when
                 * required.
                 */
                $jpeg->load( $data );
                /*
                 * The PelJpeg object contains a number of sections, one of which
                 * might be our Exif data. The getExif() method is a convenient way
                 * of getting the right section with a minimum of fuzz.
                 */
                $exif = $jpeg->getExif();
                if ( $debug )$msg .= "got the exif $eol ";

                if ( $exif == null ) {
                    /*
                     * Ups, there is no APP1 section in the JPEG file. This is where
                     * the Exif data should be.
                     */
                    if ( $debug )$msg .= self::println( 'No APP1 section found, added new.' );
                    /*
                     * In this case we simply create a new APP1 section (a PelExif * object) and adds it to the PelJpeg object.
                     */
                    $exif = new PelExif();
                    $jpeg->setExif( $exif );
                    /* We then create an empty TIFF structure in the APP1 section. */
                    $tiff = new PelTiff();
                    $exif->setTiff( $tiff );
                } else {
                    /*
                     * Surprice, surprice: Exif data is really just TIFF data! So we
                     * extract the PelTiff object for later use.
                     */
                    if ( $debug )$msg .= self::println( 'Found existing APP1 section.' );
                    $tiff = $exif->getTiff();
                }
            } elseif ( PelTiff::isValid( $data ) ) {
                    /*
                     * The data was recognized as TIFF data. We prepare a PelTiff
                     * object to hold it, and record in $file that the PelTiff object is
                     * the top-most object (the one on which we will call getBytes).
                     */
                    $tiff = $file = new PelTiff();
                    /* Now load the data. */
                    $tiff->load( $data );
                } else {
                    /*
                     * The data was not recognized as either JPEG or TIFF data.
                     * Complain loudly, dump the first 16 bytes, and exit.
                     */
                    if ( $debug )$msg .= self::println( 'Unrecognized image format! The first 16 bytes follow:', "" );
                    PelConvert::bytesToDump( $data->getBytes( 0, 16 ) );
                    return $msg;;
                }
                /*
                 * TIFF data has a tree structure much like a file system. There is a
                 * root IFD (Image File Directory) which contains a number of entries
                 * and maybe a link to the next IFD. The IFDs are chained together
                 * like this, but some of them can also contain what is known as
                 * sub-IFDs. For our purpose we only need the first IFD, for this is
                 * where the image description should be stored.
                 */
            $ifd0 = $tiff->getIfd();
            if ( $ifd0 == null ) {
                /*
                 * No IFD in the TIFF data? This probably means that the image
                 * didn't have any Exif information to start with, and so an empty
                 * PelTiff object was inserted by the code above. But this is no
                 * problem, we just create and inserts an empty PelIfd object.
                 */
                if ( $debug )$msg .= self::println( 'No IFD found, adding new.$eol' );
                $ifd0 = new PelIfd( PelIfd::IFD0 );
                $tiff->setIfd( $ifd0 );
            }
            if ( $debug )$msg .= self::println( "That compleates setup, get/adjust the data $eol " );
            /*
             * Each entry in an IFD is identified with a tag. This will load the
             * ImageDescription entry if it is present. if the IFD does not
             * contain such an entry, null will be returned.
             */
            if ( $debug )$msg .= self::println( "  -------------------------------- $eol" );
            /*
            $textList = array( "copyright", "description", "date", "keyword" ); // "width", "height");
            foreach ( $textList as $text ) {
                $tagL = self::convertText2EeixID( $text );
                $textThing = $ifd0->getEntry( $tagL );
                if ( is_null( $textThing ) ) {
                    if ( $debug )$msg .= self::println( "E#702 did not find an entry  for $text $eol" );
                } else {
                    $textValue = $textThing->getValue();
                    if ( $debug )$msg .= self::println( $textThing . rrwUtil::print_r( $textValue, true, "$text" ) );
                }
            }
            */
            if ( $debug )$msg .= self::println( "  about to get entry $eol" );
            $desc = $ifd0->getEntry( $tag );

            if ( $debug )$msg .= "for $tag found existing " .
            rrwUtil::print_r( $desc, true, "tagvalue" );
            //     We need to check if the image already had a description stored. 
            if ( $desc == null ) {
                if ( $debug )$msg .= "E#730 value was null $eol";
                //        The was no description in the image. 
                //   * In this case we simply create a new PelEntryAscii object to hold
                //   * the description. The constructor for PelEntryAscii needs to know
                //   * the tag and contents of the new entry.
                $type = self::findTagtype( $tag );
                if ( $debug )$msg .= self::println( "E#724 Adding new $tag of type $type with $newValue $eol " );
                switch ( $type ) {
                    case "Artist":
                        if ( $debug )$msg .= "trying new PelTag::ARTIST(  $eol";
                        $desc = new PelEntryAscii( PelTag::ARTIST, $newValue );
                        break;
                    case "Copyright":
                        if ( $debug )$msg .= "trying new PelEntryCopyright( $newValue ); $eol";
                        $desc = new PelEntryAscii( PelTag::COPYRIGHT,
                            $newValue );
                        break;
                    case "Description":
                    case "ImageDescription":
                        $desc = new PelEntryAscii( PelTag::IMAGE_DESCRIPTION, $newValue );
                        break;
                    case "DateTime":
                        $desc = new PelEntryAscii( PelTag::DATE_TIME, $newValue );
                        break;
                    case "HostComputer":
                        if ( $debug )$msg .= "trying new PelTag::HostComputer(  $eol";
                        $desc = new PelEntryAscii( PelTag::HOSTCOMPUTER, $newValue );
                        break;
                    case "Keywords":
                        $msg .= "trying to make keyword entry $eol";
                        $desc = new PelEntryByte( PelTag::XP_KEYWORDS, $newValue );
                        $msg .= "created keyword entry $eol";
                        break;
                    case "UserComment":
                        $desc = new PelEntryAscii( PelTag::USERCOMMENT, $newValue );
                        break;
                     case "XPComment":
                        $desc = new PelEntryAscii( PelTag::XPCOMMENT, $newValue );
                        break;
             
                    default:
                        throw new Exception( "E#488 Unknown self::findTagtype for $type" );
                        break;
                }
                //    * This will insert the newly created entry with the description
                //    * into the IFD.
                if ( $debug )$msg .= rrwUtil::print_r( $desc, true . "addng tag thing" );
                $ifd0->addEntry( $desc );
                if ( $debug )$msg .= "added the entry $eol";
            } else {
                //     An old description was found in the image. 
                $oldDescription = rrwUtil::print_r( $desc->getValue(), true, "existng" );
                if ( $debug )$msg .= self::println( "$tag entry from $oldDescription" ) . $eol;
                if ( $debug )$msg .= self::println( "$tag entry TO &nbsp; " . $newValue ) . $eol;
                $desc->setValue( $newValue );
                // The description is simply updated with the newValue. 

            }
            /*
             * At this point the image on disk has not been changed, it is only
             * the object structure in memory which represent the image which has
             * been altered. This structure can be converted into a string of
             * bytes with the getBytes method, and saving this in the output file
             * completes the script.
             */
            if ( $debug )$msg .= self::println( 'Writing file "%s".', $output );
            $file->saveFile( $output ) . $eol;
            if ( $debug )$msg .= self::println( "finished with test Pel $eol" );
            if ( $debug )$msg .= self::println( "  -------------------------------- end PHPel  $eol" );
        } catch ( Exception $ex ) {
            $code = $ex->getCode();
            if ( 0 == $code )
                $msg .= "$errorBeg E#400 main routine catch 
                with no message $errorEnd";
            else
                $msg .= "$errorBeg" . $ex->get_message() .
            "E#400 main routine catch $errorEnd"; // . 
        }
        return $msg;
    }

    private static function convertText2EeixID( $text ) {
        // codes from https://www.exiftool.org/TagNames/EXIF.html
        global $eol, $errorBeg, $errorEnd;
        // print " self::convertText2EeixID( $text )  $eol ";
        switch ( $text ) {
            case "Artist":
                return 0x013b;
            case "Copyright":
                return 0x8298;
            case "ImageDescription":
            case "Image_Description":
                return 0x010e;
            case "DateTime":
            case "DateTimeOriginal":
                return 0x0132; // Peltag::DATETIMEOROGINAL ;  // 0x0132 //  0x9003;
            case "HostComputer":
                return 0x013c;
            case "Keywords":
                return 0x9c9e; // (WindowsXPKeywords)
            case "width":
                return 0x0100; //"IMAGEWIDTH";  
            case "height":
                return 0x0101; // 0xbc81
            case "COMPUTED":
                return "COMPUTED"; // 0xbc81
            case "UserComment":
                return 0x9286;
            case "XPComment":
                return 0x9c9c;
            default:
                debug_print_backtrace( 0, 3 );
                throw new Exception( "$errorBeg E#484 Invalid Tag Nameof '$text' $errorEnd" );
        }
        // assert never  get here.
    }

    private static function findTagtype( $tag ) {
        global $eol, $errorBeg, $errorEnd;
        switch ( $tag ) {
            // foramt names in class PelFormat, 
            //   should match switch ( $type ) abour line 326
            case 0x8298: // copyright
                $out = "Copyright";
                break;
            case 0x13b: // Artist
                $out = "Artist";
                break;
            case 0x010e: // Image Description
                $out = "ImageDescription";
                break;
            case 0x0132: // modify date
                $out = "DateTime";
                break;
            case 0x9c9e: // (WindowsXPKeywords)
                $out = "Keywords";
                break;
                /*
            case 0x9003: //Datetime original
            case 0x9004: //CreateDate
                return "asciixxxxxx"; // string
            case 0x9c9c: // comment
            case 0x9c9d: // Author
            case 0x9c9f: // Subject
                return "bytexxxxx"; // int8u
                //       case PelTag::GPS_LATITUDE:
                //        case PelTag::GPS_LONGITUDE:
                //        case GPS_ALTITUDE:
                //            return "rational";
                /*    case "date":
            return "int"; // Peltag::DATETIMEOROGINAL ;  // 0x0132 //  0x9003;
        case "width":
        case "height":
            return "no";
        case "COMPUTED":
            return "COMPUTED"; // 0xbc81
        case "datetimedigitized":
            return "Ascii";
*/
            default:
                $hex = dechex( $tag );
                throw new Exception( "E#695 looking for typeof exif[$tag]
            of exif[$hex]", -695 );
        }
        return $out;
    }

    private static function doOneItem( $item, $value ) {
        global $testPath, $eol, $errorBeg, $errorEnd;
        $input_file = "$testPath/input.jpg";
        $msg = self::doAnItem( $input_file, $item, $value );
        $msg .= "================================================= $item -- $value $eol";
        return $msg;
    }

    private static function doSecondItem( $item, $value ) {
        global $testPath, $eol, $errorBeg, $errorEnd;
        $input_file = "$testPath/output.jpg";
        $msg = self::doAnItem( $input_file, $item, $value );
        $msg .= "222222222222222222222222222222222222222222222222222 $item -- $value $eol";
        return $msg;
    }

    private static function doAnItem( $input_file, $item, $value ) {
        global $testPath, $eol, $errorBeg, $errorEnd;
        $msg = "";
        // lnowntags for the 5th parameter 
        $output_file = "$testPath/output.jpg";
        // Copyright info to add
        $msg .= self::changeItem( $input_file, $output_file, $item, $value );
        if ( false ) { // write .txt verion of the files
            $buf1 = file_get_Contents( $input_file );
            $buf1out = bin2hex( $buf1 );
            $fp = fopen( "$input_file.txt", "w" );
            fwrite( $fp, $buf1out );
            fclose( $fp );

            $buf1 = file_get_Contents( $output_file );
            $buf1out = bin2hex( $buf1 );
            $fp = fopen( "$output_file.txt", "w" );
            fwrite( $fp, $buf1out );
            fclose( $fp );
        }
        if ( file_exists( $output_file ) ) {
            $Exif = exif_read_data( $output_file );
            $checkValue = $Exif[ $item ];
        } else {
            $checkValue = "file does not exist";
        }
        if ( $checkValue == $value )
            $msg .= "<span style='background-color:lightgreen;' >Sucessfully update</span> the exif$eol";
        else
            $msg .= "$errorBeg E#720 proposed value '$value', resultant value 
                            '$checkValue' $errorEnd";
        $msg .= self::dumpMeta( $input_file, $output_file );
        return $msg;
    }

    private static function changeItem( $input_file, $output_file, $item, $newVale ) {
        global $testPath, $eol, $errorBeg, $errorEnd;
        global $testPath, $eol;
        $msg = "";
        $tag = self::convertText2EeixID( $item );
        $argv = array( "self::rrwPHPel",
            //        "-d",
            $input_file,
            $output_file,
            $tag,
            $newVale,
        );
        $msg .= self::rrwPHPel( $argv );
        return $msg;
    }

    function testpel() {
        global $testPath, $eol;
        $msg = "";
        $output_file = "$testPath/output.jpg";
        $output_file6 = "$testPath/a6.jpg";
        foreach ( array( "$testPath/input.jpg", ) as $input_file ) {
            $output_file = str_replace( ".jpg", "", $input_file ) . "new.jpg";
            $msg .= self::changeItem( $input_file, $output_file, "Artist",
                "a change in ImageDescription" );
            $msg .= rrwExif::dumpMeta( $input_file, $output_file );
            $msg .= self::changeItem( $input_file, $output_file, "copyright",
                "a change in copyright" );
            $msg .= rrwExif::dumpMeta( $input_file, $output_file );
            $msg .= self::changeItem( $input_file, $output_file, "keyword",
                "a change in keyword in output" );
            $msg .= rrwExif::dumpMeta( $input_file, $output_file );
            $msg .= self::changeItem( $output_file, $output_file, "keyword",
                "a change in keyword in a6" );
            $msg .= rrwExif::dumpMeta( $output_file, $output_file );
        }
        return $msg;
    }

    private static function testpel2( $attr ) {
        global $gallery, $eol;
        $msg = "";
        $msg .= SetConstants( "testPel" );
        // lnowntags for the 5th parameter 
        $input_file = "/home/pillowan/www-shaw-weil-pictures/photos/028682_ms5-73_011798_cr.jpg";
        //  $output_file = "/home/pillowan/www-shaw-weil-pictures/photos/028682_ms5-73_011798_cr.jpg";
        //  $input_file = "$gallery/a3.jpg";
        $output_file = "$gallery/a5.jpg";
        // Copyright info to add
        $copyright = "Mary Shaw";
        $msg .= changeCopyRight( $input_file, $output_file, $copyright );
        $msg .= rrwExif::dumpMeta( $input_file, $output_file );
        return $msg;
    }
    private static function testpel3() {
        global $testPath, $eol;
        $msg = "";
        try {
            $output_file = "$testPath/output.jpg";
            $input_file = "$testPath/input.jpg";
            $tag = self::convertText2EeixID( "desc" ); // works
            $tag = self::convertText2EeixID( "Copyright" ); // works on a2=4
            $tag = self::convertText2EeixID( "Keywords" ); // works
            $tag = self::convertText2EeixID( "ImageDescription" ); // works
            $tag = self::convertText2EeixID( "keywords" ); // fails
            $tag = self::convertText2EeixID( "copyright" ); // fails
            $argv = array( "self::rrwPHPel",
                //       "-d",
                $input_file,
                $output_file,
                $tag,
                "a change in the data ",
            );
            $msg .= self::rrwPHPel( $argv );
        } catch ( Exception $ex ) {
            $msg .= "E#400 testpel3 catch " . $ex->get_message();
        }
        $msg .= rrwExif::dumpMeta( $input_file, $output_file );
        return $msg;
    }
    private function truncate( & $item, $key ) {
        if ( strlen( $item ) > 120 )
            $item = substr( $item, 0, 120 );
    }

    private static function dumpMeta( $file1 = "", $file2 = "" ) {
        global $eol, $errorBeg, $errorEnd;
        global $testPath;
        $msg = "";

        if ( "" == $file1 )
            $file1 = "$testPath/input.jpg";
        if ( "" == $file2 )
            $file2 = "$testPath/output.jpg";

        $temp1 = self::rrw_exif_read_data( $file1 );
        $temp1 = rrwUtil::print_r( $temp1, true, " file $file1" );
        array_walk_recursive( $temp1, 'truncate' );
        $temp1size = filesize( $file1 );
        if ( !file_exists( $file2 ) ) {
            $msg .= "E#460 file does not exist $eol";
            $temp2 = "$eol $file2 $eol $errorBeg E#460 file does not exist $errorEnd";
            $temp2Size = 0;
        } else {
            $temp2 = self::rrw_exif_read_data( $file2 );
            $temp2 = rrwUtil::print_r( $temp2, true, " file $file2" );
            array_walk_recursive( $temp2, 'truncate' );
            $temp2size = filesize( $file2 );
        }
        $msg .= "<table>" . rrwFormat::HeaderRow( "$file1 - $temp1size
                        bytes", "$file2 - $temp2size bytes" );
        $msg .= rrwFormat::CellRow( $temp1, $temp2 );
        $msg .= "</table>";
        return $msg;
    }

    public static function rrw_exif_read_data( $file ) {
        error_reporting( E_ALL ^ E_WARNING );
        $exifArray = exif_read_data( $file );
        error_reporting( E_ALL || E_STRICT );
        return $exifArray;
    }

    function readinputa4( $attr ) {
        // reads and displays the meta data for file f1, f2
        ini_set( "display_errors", true );
        global $testPath, $eol;
        $msg = "";
        $input_file = "$testPath/input.jpg";
        $output_file = "$testPath/output.jpg";
        $msg .= rrwExif::dumpMeta( $input_file, $output_file );
        return $msg;
    }
    private static function Artist() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Artist", "Roy Weil" );
        return $msg;
    }
    private static function comment() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "comment", "Roy Weil" );
        return $msg;
    }
    private static function copyright() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Copyright", "Mary Shaw" );
        return $msg;
    }
    private static function CopyArtist() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Copyright", "Mary Shaw" );
        $msg .= self::doSecondItem( "Artist", "Roy Weil" );
        $msg .= self::doSecondItem( "ImageDescription", "Armstrong Trail, photographer: Roy Weil, keyworrds:tunnel, water, bench" );
        return $msg;
    }
    private static function copycopy() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Copyright", "Mary Shaw" );
        $msg .= self::doSecondItem( "Copyright", "Roy Weil" );
        return $msg;
    }

    private static function DateTime() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "DateTime", "2009-02-02" );
        return $msg;
    }
    private static function description() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "ImageDescription", "This is a descripiion" );
        return $msg;
    }
    private static function keyword() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Keywords", "some, keyword, multy word" );
        return $msg;
    }
    private static function HostComputer() {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "HostComputer", "computer host" );
        return $msg;
    }
    private static function test1( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = self::doOneItem( "Artist", "Roy Weil" );
        return $msg;
    }
    private static function test2( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        return $msg;
    }

    function example_dirsort( $newdescription ) {
        // rename files in a directory to matxhdte time
        // not useful to us

    }
   public static function test11($attr) {   
       global $eol, $errorBeg, $errorEnd;
        global $testPath;
        $msg = "";
        include "setConstants.php";
        $testPath = "/home/pillowan/www-shaw-weil-pictures-dev/testphoto";
        $output_file = "$testPath/output.jpg";
        //       $msg .= rrwUtil::print_r( $attr, true, "I#739 atributes" );
        $task = rrwPara::String( "task", $attr );
        print "I#735 testing the task $task $eol";
        if ( file_exists( $output_file ) )
            unlink( $output_file );
        switch ( $task ) {
            case "artist":
                $msg .= self::Artist();
                break;
            case "copyartist":
                $msg .= self::CopyArtist();
                break;
            case "copycopy":
                $msg .= self::copycopy();
                break;
            case "copyright":
                $msg .= self::copyright();
                break;
            case "datetime":
                $msg .= self::DateTime();
                break;
            case "description":
                $msg .= self::description();
                break;
            case "dumpmeta":
                $msg .= self::dumpmeta();
                break;
            case "example_editDescription":
                print "calling example_editDescription $eol";
                $msg .= self::example_editDescription( " a new description" );
                break;
            case "HostComputer":
                $msg.= self::HostComputer();
                break;
            case "keyword":
                $msg .= self::keyword();
                break;
            case "testpel":
                $msg .= self::testpel();
                break;
            case "testpel2":
                $msg .= self::testpel2();
                break;
            case "testpel3":
                $msg .= self::testpel3();
                break;
            case "writeoutput":
                $msg .= self::writeoutput();
                break;
            default:
                $msg .= "$errorBeg task of '$task' was not found$errorEnd";
                break;
        } // end switch}
       return $msg;
   }

    private static function example_editDescription( $newValue ) {
        global $eol, $errorBeg, $errorEnd;
        global $testPath;
        $msg = "";

        $input = "$testPath/input.jpg";
        $output = "$testPath/output.jpg";

        /*
         * The input file is now read into a PelDataWindow object. At this
         * point we do not know if the file stores JPEG or TIFF data, so
         * instead of using one of the loadFile methods on PelJpeg or PelTiff
         * we store the data in a PelDataWindow.
         */
        $msg .= self::println( 'Reading file "%s".', $input );
        $data = new PelDataWindow( file_get_contents( $input ) );

        /*
         * The static isValid methods in PelJpeg and PelTiff will tell us in
         * an efficient maner which kind of data we are dealing with.
         */
        if ( PelJpeg::isValid( $data ) ) {
            /*
             * The data was recognized as JPEG data, so we create a new empty
             * PelJpeg object which will hold it. When we want to save the
             * image again, we need to know which object to same (using the
             * getBytes method), so we store $jpeg as $file too.
             */
            $jpeg = $file = new PelJpeg();

            /*
             * We then load the data from the PelDataWindow into our PelJpeg
             * object. No copying of data will be done, the PelJpeg object will
             * simply remember that it is to ask the PelDataWindow for data when
             * required.
             */
            $jpeg->load( $data );

            /*
             * The PelJpeg object contains a number of sections, one of which
             * might be our Exif data. The getExif() method is a convenient way
             * of getting the right section with a minimum of fuzz.
             */
            $exif = $jpeg->getExif();

            if ( $exif == null ) {
                /*
                 * Ups, there is no APP1 section in the JPEG file. This is where
                 * the Exif data should be.
                 */
                $msg .= self::println( 'No APP1 section found, added new.' );

                /*
                 * In this case we simply create a new APP1 section (a PelExif
                 * object) and adds it to the PelJpeg object.
                 */
                $exif = new PelExif();
                $jpeg->setExif( $exif );

                /* We then create an empty TIFF structure in the APP1 section. */
                $tiff = new PelTiff();
                $exif->setTiff( $tiff );
            } else {
                /*
                 * Surprice, surprice: Exif data is really just TIFF data! So we
                 * extract the PelTiff object for later use.
                 */
                $msg .= self::println( 'Found existing APP1 section.' );
                $tiff = $exif->getTiff();
            }
        } elseif ( PelTiff::isValid( $data ) ) {
            /*
             * The data was recognized as TIFF data. We prepare a PelTiff
             * object to hold it, and record in $file that the PelTiff object is
             * the top-most object (the one on which we will call getBytes).
             */
            $tiff = $file = new PelTiff();
            /* Now load the data. */
            $tiff->load( $data );
        } else {
            /*
             * The data was not recognized as either JPEG or TIFF data.
             * Complain loudly, dump the first 16 bytes, and exit.
             */
            $msg .= self::println( 'Unrecognized image format! The first 16 bytes follow:' );
            PelConvert::bytesToDump( $data->getBytes( 0, 16 ) );
            exit( 1 );
        }

        /*
         * TIFF data has a tree structure much like a file system. There is a
         * root IFD (Image File Directory) which contains a number of entries
         * and maybe a link to the next IFD. The IFDs are chained together
         * like this, but some of them can also contain what is known as
         * sub-IFDs. For our purpose we only need the first IFD, for this is
         * where the image description should be stored.
         */
        $ifd0 = $tiff->getIfd();

        if ( $ifd0 == null ) {
            /*
             * No IFD in the TIFF data? This probably means that the image
             * didn't have any Exif information to start with, and so an empty
             * PelTiff object was inserted by the code above. But this is no
             * problem, we just create and inserts an empty PelIfd object.
             */
            $msg .= self::println( 'No IFD found, adding new.' );
            $ifd0 = new PelIfd( PelIfd::IFD0 );
            $tiff->setIfd( $ifd0 );
        }

        /*
         * Each entry in an IFD is identified with a tag. This will load the
         * ImageDescription entry if it is present. If the IFD does not
         * contain such an entry, null will be returned.
         */
        $desc = $ifd0->getEntry( PelTag::IMAGE_DESCRIPTION );

        /* We need to check if the image already had a ImageDescription stored. */
        if ( $desc == null ) {
            /* The was no description in the image. */
            $msg .= self::println( 'Added new IMAGE_DESCRIPTION entry with "%s".', $newValue );

            /*
             * In this case we simply create a new PelEntryAscii object to hold
             * the description. The constructor for PelEntryAscii needs to know
             * the tag and contents of the new entry.
             */
            $desc = new PelEntryAscii( PelTag::IMAGE_DESCRIPTION, $newValue );

            /*
             * This will insert the newly created entry with the description
             * into the IFD.
             */
            $ifd0->addEntry( $desc );
        } else {
            /* An old description was found in the image. */
            $msg .= self::println( 'Updating IMAGE_DESCRIPTION entry from "%s" to "%s".', $desc->getValue(), $newValue );

            /* The description is simply updated with the new description. */
            $desc->setValue( $newValue );
        }

        /*
         * At this point the image on disk has not been changed, it is only
         * the object structure in memory which represent the image which has
         * been altered. This structure can be converted into a string of
         * bytes with the getBytes method, and saving this in the output file
         * completes the script.
         */
        $msg .= self::println( 'Writing file "%s".', $output );
        $file->saveFile( $output );
        $exif = exif_read_data( $output );
        $msg .= rrwExif::dumpMeta( $input, $output );
        return $msg;
    }
 }  // end class
?>
