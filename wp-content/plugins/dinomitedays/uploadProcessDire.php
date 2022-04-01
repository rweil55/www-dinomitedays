<?php

class uploadProcessDire {

    public static function upload( $attr ) {
        global $eol, $errorBeg, $errorEnd;
        global $uploadPath;
        // looks for files in the upload directory 
        //      creates database record if not already there
        //      creates the _cr version with bottom line credit
        //      creates the thumbnail version
        //      extracts the exif data to the database
        //      moves to the high_resoltion directory
        //
        $msg = "";
        $debug = rrwUtil::setDebug( "upload" );

        try {
            if ( $debug )$msg .= "uploadProcessDire ($uploadPath) $eol";
            include "setConstants.php";

            $uploadshortname = rrwPara::String( "uploadshortname", $attr );
            if ( $debug )$msg .= "found $uploadshortname in the calling parameters $eol";


            if ( !empty( $uploadshortname ) ) {
                $msg .= self::ProcessOneFile( $uploadshortname );
                $photoname = strToLower( $uploadshortname );
                $cntUploaded = 1;
            } else {
                // not a single file request. walk the directory
                $handle = opendir( $uploadPath );
                if ( !is_resource( $handle ) )
                    throw new Exception( "$errorBeg E#711 failed to 
                                    open $uploadPath $errorEnd" );
                if ( $debug )$msg .= "Entries:$eol";
                $cnt = 0;
                $cntUploaded = 0;
                while ( false !== ( $uploadshortname = readdir( $handle ) ) ) {
                    $cnt++;
                    if ( $cnt > 600 )
                        break;
                    if ( is_dir( "$uploadPath/$uploadshortname" ) )
                        continue; // ignore directories
                    if ( $debug )$msg .= "found $uploadshortname in the diretory search $eol";
                    $msg .= self::ProcessOneFile( $uploadshortname );
                    $photoname = strToLower( $uploadshortname );
                    $cntUploaded++;
                    break;
                } // end while

            } // end  if (! empty($uploadentry))
            if ( 1 == $cntUploaded ) {
                $photoname = substr( $photoname, 0, -4 );
                if ( $debug )$msg .= "DisplayOne( array( \"photoname\" => $photoname ) ) $eol";
                $msg .= freeWheeling_DisplayOne::DisplayOne(
                    array( "photoname" => $photoname ) );
            } else {
                $msg .= "$eol uploaded $cntUploaded files $eol";
            }
        } // end try
        catch ( Exception $ex ) {
            $msg .= $errorBeg . $ex->getMessage() . $errorEnd;
        }
        return $msg;
    } // end function uploadProcessDire::upload

    private static function processOneFile( $entry ) {
        global $eol, $errorBeg, $errorEnd;
        global $uploadPath;
        global $wpdbExtra, $rrw_photos;
        $msg = "";
        $debug = rrwUtil::setDebug( "onefile" );

        if ( $debug )$msg .= "$entry, ";
        $sourceFile = "$uploadPath/$entry"; // in uplosd dire
        // ------new ----------------------------  validate photoname
        if ( !file_exists( $sourceFile ) )
            throw new Exception( "$msg $errorBeg E#717 processOneFile( $entry )
                     file not found in upload $errorEnd" );
        $mime_type = mime_content_type( $sourceFile );
        switch ( $mime_type ) {
            case 'image/jpeg':
                //    case 'image/png':
                //    case 'image/gif':
                break; // is good
            default:
                throw new RuntimeException( "file '$sourceFile' 
                        minetype is $mime_type, 
                        it should .jpg, " /*.png or .gif"*/ );
        }
        $fileExif = exif_read_data( $sourceFile ); // used to get time
        $photoname = substr( $entry, 0, strlen( $entry ) - 4 );
        $photoname = strtolower( $photoname );
        if ( $debug )$msg .= "photoname just aftercreate $photoname $eol";
        $pregResults = preg_match( "/[-a-zA-z0-9 _]*/",
            $photoname, $matchs );
        if ( 1 != count( $matchs ) )
            throw new RuntimeException( "file name can consist of only
                letters, numbers, and spaces" );
        // --------------------------- deal with database entry
        $Data = array(
            "filename" => $photoname,
            "highresShortname" => $entry,
            "uploaddate" => date( "Y-m-d H:i" ),
            /* all others default to blank */
        );
        $remotefile = rrwPara::String( "remotefile" );
        if ( !empty( $remotefile ) )
            $Data[ "Direonp" ] = $remotefile;

        $sqlRec = "select * from $rrw_photos 
                        where photoname = '$photoname'";
        $recs = $wpdbExtra->get_resultsA( $sqlRec );

        if ( 1 == $wpdbExtra->num_rows ) {
            // have meta data, update it   
            if ( $debug )$msg .= rrwUtil::print_r( $Data, true,
                "updating $photoname $eol" );
            $key = array( "photoname" => $photoname );
            $cnt = $wpdbExtra->update( $rrw_photos, $Data, $key );
            if ( 1 != $cnt ) {
                $err = "$errorBeg E#702 update no change $errorEnd";
                $msg .= rrwUtil::print_r( $Data, true, $err );
            }
        } else {
            // no meta data
            $Data[ "photoname" ] = $photoname;
            $Data[ "photographer" ] = "Mary Shaw";
            if ( $debug )$msg .= rrwUtil::print_r( $Data, true,
                "inserting $photoname $eol" );
            $cnt = $wpdbExtra->insert( $rrw_photos, $Data );
            if ( 1 != $cnt ) {
                $err = "$errorBeg E#716 insert fails $errorEnd";
                $msg .= rrwUtil::print_r( $Data, true, $err );
            }
        }

        $sqlRec = "select * from $rrw_photos 
                        where photoname = '$photoname'";
        $recs = $wpdbExtra->get_resultsA( $sqlRec );
        $recOld = $recs[ 0 ];
        $photographer = $recOld[ "photographer" ];

        $msg .= freewheeling_fixit::sourceReject( $photoname, "use" );

        $msg .= self::makeImages( $sourceFile, $photographer );


        // meta date exists make it consistant with the EXIF
        $msg .= freewheeling_fixit::fixAssumeDatabaseCorrect( $recOld );
        if ( $debug )$msg .= "getting date $eol";
        $photoDate = freewheeling_fixit::getPhotoDateTime( $fileExif );
        if ( !empty( $photoDate ) ) {
            if ( $debug )$msg .= "photoDate now $photoDate";
            $sqlTimeUpdate = "update $rrw_photos set photodate = '$photoDate'
                                where photoname = '$photoname'";
            $wpdbExtra->query( $sqlTimeUpdate );
        }
        return $msg;
    } // end function processOneFile


    private static function makeImages( $sourceFile, $photographer ) {
        // assume the file is a temp location - gone when done
        global $eol, $errorBeg, $errorEnd;
        global $photoUrl, $photoPath, $thumbUrl, $thumbPath, $highresUrl, $highresPath;
        //      creates the _cr version with bottom line credit of photographer
        //      creates the thumbnail version
        //      moves to the high_resoltion directory
        $msg = "";
        $debug = rrwUtil::setDebug( "makeimage" );
        $debugImageWork = rrwUtil::setDebug( "imagework" );
        try {
            if ( $debug )$msg = "makeImages( $sourceFile, $photographer ) $eol";
            $desiredW = 200; #	force thumbnail width to this number
            $maxHeight = 700; // limit display mage to yhis number
            $h_botWhite = 20; #	height of the white bar at the bottom for copyright notice
            $fontSize = 12; #	height of the copyright text
            $fontfile = "arial.ttf";
            $fontDire = "/home/pillowan/www-shaw-weil-pictures/wp-content/plugins/roys-picture-processng";
            $fontfile = "$fontDire/mvboli.ttf";

            $fileSplit = pathinfo( $sourceFile );
            if ( $debug )$msg .= rrwUtil::print_r( $fileSplit, true, "the file split" );
            $extension = $fileSplit[ 'extension' ];
            $basename = $fileSplit[ 'basename' ];
            $photoname = $fileSplit[ 'filename' ];
            $photoname = strToLower( $photoname );
            $FullfileHighRes = "$highresPath/$basename";
            $fullfileThumb = "$thumbPath/$photoname" . "_tmb.jpg";
            $fullFilePhoto = "$photoPath/$photoname" . "_cr.jpg";
            if ( $debug ) {
                $msg .= "base name : " . $fileSplit[ 'basename' ] . $eol;
                $msg .= "extension : " . $fileSplit[ 'extension' ] . $eol;
                $msg .= "FullfileHighRes : $FullfileHighRes $eol";
                $msg .= "fullfileThumb : $fullfileThumb $eol";
                $msg .= "fullFilePhoto : $fullFilePhoto $eol";
            }

            if ( !rename( $sourceFile, $FullfileHighRes ) ) {
                throw new Exception( " $errorBeg $msg E#813 while attempting 
                move ($sourceFile, $FullfileHighRes) $errorEnd" );
            }
            if ( !file_exists( $FullfileHighRes ) ) {
                $msg .= "errorBeg Full Resolution did not get moved $errorEnd $eol 
                $FullfileHighRes $eol";
                return $msg;
            }
            if ( $debug )$msg .= "saved the source file in $FullfileHighRes $eol";

            //  ------------------------------------- got the file now process it
            if ( !file_exists( $fontfile ) ) {
                $msg .= "bad font $fontfile ";
                throw new Exception( "$msg $errorBeg E#812 Problems with the font file $errorEnd" );
            }

            // create new dimensions, keeping aspect ratio
            $imageInfo = getimagesize( $FullfileHighRes );
            $w_src = $imageInfo[ 0 ];
            $h_src = $imageInfo[ 1 ];

            if ( $h_src == 0 || $w_src == 0 ) {
                throw new Exception( " $errorBeg width is $w_src or Height is $h_src zero $errorEnd" );
            }
            if ( $debugImageWork )$msg .= "input inmage is $w_src X $h_src ... ";
            # load the image into core
            $mime_type = mime_content_type( $FullfileHighRes );
            if ( true ) { // $imagick ) 
                $im_src = new Imagick();
                $im_src->readimage( $FullfileHighRes );
                $im_src->scaleImage( 200, 0 );
                $im_src->writeImage( $fullfileThumb );
                $im_src->destroy();
                if ( $debugImageWork )$msg .= "Created thumpnail $fullfileThumb $eol";
                $im_src = new Imagick();
                $a = $im_src->getversion();
                $im_src->readimage( $FullfileHighRes );
                if ( $h_src > $maxHeight ) {
                    $im_src->scaleImage( 0, $maxHeight );
                }
                if ( $debugImageWork )$msg .= "Created internal image $eol";
                if ( !empty( $photographer ) ) {
                    if ( $debugImageWork )$msg .= "adding photographer $eol";
                    $text = "Photo by $photographer";
                    $h_new = $im_src->getImageHeight();
                    $w_new = $im_src->getImageWidth();
                    $h_new = $h_new + $h_botWhite;
                    $im_src->extentImage( $w_new, $h_new, 0, 0 );

                    $draw = new ImagickDraw();
                    $draw->setStrokeColor( "#00000000" );
                    $draw->setFillColor( "black" );
                    $draw->setStrokeWidth( 0 );
                    //   $draw->setFont( $fontfile );
                    $draw->setFontSize( $fontSize );
                    $metrics = $im_src->queryFontMetrics( $draw, $text );
                    $baseline = $h_new - ( ( $h_botWhite - $fontSize ) / 2 );
                    $marginLeft = ( $w_new - $metrics[ "textWidth" ] ) / 2;
                    $draw->annotation( $marginLeft, $baseline, $text );
                    $im_src->drawImage( $draw );
                    $draw->destroy();
                }
                $im_src->writeImage( $fullFilePhoto );
                $im_src->destroy();
                if ( $debugImageWork )$msg .= "made image $photoUrl/$photoname $eol
                <img src='$photoUrl/$photoname" . "_cr.jpg'/> ";
                return $msg;

            } else {
                switch ( $mime_type ) {
                    case "image/gif": //   gif -> jpg
                        $img_src = imagecreatefromgif( $FullfileHighRes );
                        break;
                    case "image/jpg": //   jpeg -> jpg
                    case "image/jpeg": //   jpeg -> jpg
                        $img_src = imagecreatefromjpeg( $FullfileHighRes );
                        break;
                    case "image/png": //   png -> jpg
                        $img_src = imagecreatefrompng( $FullfileHighRes );
                        break;
                    default:
                        throw new Exception( " $errorBeg File '$FullfileHighRes' is
                    mime_type, only GIF, JPG, jpeg or PNG are allowed 
                    $errorEnd" );
                }
                //  ------------------------------------------- resize thumbnail
                $aspect = $h_src / $w_src; // maintain the aspect ration
                $w_tmb = $desiredW;
                $h_tmb = $w_tmb * $aspect;
                if ( $debug )$msg .= "resizetmb $w_src, $h_src, $w_tmb, $h_tmb$eol ";
                $img_tmb =
                    self::resizeImage( $img_src, $w_src, $h_src, $w_tmb, $h_tmb );
                if ( !imagejpeg( $img_tmb, $fullfileThumb ) ) //  save new image
                    throw new Exception( " E#816 imagejpeg($img_tmb, 
                                    $fullfileThumb ) failed " );
                if ( $debugImageWork )$msg .= " thumbnail saved to $fullfileThumb $eol";
                imagedestroy( $img_tmb ); // free memory
                // --------------------------------------------- resize copyright
                $maxHeight = 768;
                $aspect = $h_src / $w_src;
                if ( $h_src > $maxHeight ) {
                    $h_cr = $maxHeight;
                    $w_cr = $h_cr / $aspect;
                    $img_copyright =
                        self::resizeImage( $img_src, $w_src, $h_src, $w_cr, $h_cr );
                    if ( $debugImageWork )$msg .= "aspect = $aspect image 
                    resized $w_src X $h_src to $w_cr X $h_cr $eol";

                } else {
                    $img_copyright = $img_src;
                    $h_cr = $h_src;
                    $w_cr = $w_src;
                }
                // img_copywite has been created.
                if ( empty( $photographer ) ) {
                    // skip adding the bottom line, just write the file
                    if ( $debugImageWork )$msg .= "about to write file to $fullFilePhoto ...";
                    if ( !imagejpeg( $img_copyright, $fullFilePhoto ) )
                        throw new Exception( "$msg $errorBeg E#621 
                        imagejpeg( $img_copyright, $fullFilePhoto )$errorEnd" );
                    if ( $debugImageWork )$msg .= " imageno border saved to $fullFilePhoto $eol";
                    if ( !imagedestroy( img_copyright ) )
                        throw new Exception( "$msg $errorBeg E#623 
                            imagedestroy( img_copyright ) $errorEnd" );
                } else {
                    //  ------------------------------------------------ place text
                    $h_new = $h_cr + $h_botWhite;
                    $imgFinal = imagecreatetruecolor( $w_cr, $h_new );
                    if ( !imagecopy( $imgFinal, $img_copyright,
                            0, 0, 0, 0, $w_src, $h_cr ) )
                        throw new Exception( "E#404
                            imagecopy( $imgFinal, $img_copyright,
                                0, 0, 0, 0, $w_src, $h_cr  failed" );
                    $white = imagecolorallocate( $imgFinal, 255, 255, 255 );
                    if ( $white === false )
                        throw new Exception( "E#639
                    imagecolorallocate($imgFinal, 255,255,0)" );
                    $black = imagecolorallocate( $imgFinal, 0, 0, 0 ); # returns zero
                    if ( $black === false )
                        throw new Exception( "E#648
                    imagecolorallocate($imgFinal, 0,0,0)" );

                    if ( !imagefilledrectangle( $imgFinal, 0, $h_cr, $w_src, $h_new, $white ) )
                        throw new Exception( " $msg E#629
                    imagerectangle(... 0, $h_cr,$w_src, $h_new, $white ) " );
                    $left = 2;
                    $top = $h_cr + 2;
                    $bot = $h_new - 3;
                    $copyrightMsg = "Photo by $photographer"; //pictures.shaw-weil.com";
                    // use true type fonts. First deterine the actual length
                    $bounds = imagettftext( $imgFinal, $fontSize, 0, $left, $bot, $black, $fontfile, $copyrightMsg );
                    if ( $bounds === false )
                        throw new Exception( "E#634 imagettftext ($img_copyright,
                                $fontSize, 0, $left, $bot, 
                                $black,$fontfile,$copyrightMsg)" );
                    $w_text = abs( $bounds[ 0 ] - $bounds[ 2 ] );
                    $h_text = abs( $bounds[ 1 ] - $bounds[ 5 ] );
                    $left = ( $w_cr - $w_text ) / 2; # center the text left to right
                    if ( $left < 2 )$left = 2;
                    $bot = $h_new - 3; # just barely off the bottom.
                    # black out the text just drawn, and redraw it centered on the visiable image
                    $tmpHeight = $h_cr - $h_botWhite;
                    if ( !imagefilledrectangle( $imgFinal, 0, $h_cr, $w_src, $h_new, $white ) )
                        throw new Exception( "imagefilledrectangle( imgFinal, 0, $h_cr, $w_src $h_new, $white ) " );
                    $bounds = imagettftext( $imgFinal, $fontSize, 0, $left, $bot, $black, $fontfile, $copyrightMsg );
                    if ( $bounds === false )
                        throw new Exception( "imagettftext (img_copyright, $fontSize,               0, $left, $bot, $black, $fontfile,
                                    $copyrightMsg)" );
                    if ( !imagejpeg( $imgFinal, $fullFilePhoto ) )
                        throw new Exception( "imagejpeg( imgFinal, fullFilePhoto " );
                    if ( $debugImageWork )$msg .= " copyright border saved to $fullFilePhoto $eol";

                    if ( !imagedestroy( $imgFinal ) )
                        throw new Exception( "imagedestroy( img_copyright " );
                    if ( !imagedestroy( $img_src ) ) // free memory
                        throw new Exception( "$msg E#628 imagedestroy( img_sec )" );
                } // end of drawing photographer name
            } // end of if imagmik 
        } // end try
        catch ( Exception $ex ) {
            $msg .= $errorBeg . $ex->getMessage() . $errorEnd;
        }
        return $msg;
    } // end function MakeImakeImages 


    private static function resizeImage( $img_in, $curWidth, $curHeight,
        $w_new, $h_new ) {
        global $eol;

        $debugResize = true;
        $debugExif = true;

        $scalefactor = $w_new / $curWidth;

        $imgout = imagescale( $img_in, $scalefactor );
        if ( false === $imgout )
            throw new Exception( "E#651 failure in resize usine $scalefactor
            as scale factor or $w_new/ $curWidth " );
        return $imgout;


        $img_out = imagecreatetruecolor( $w_new, $h_new )
        or die( "imagecreatetruecolor($w_new, $h_new)" );
        imagecopyresampled( $img_out, $img_in, 0, 0, 0, 0, $w_new, $h_new, $curWidth, $curHeight )
        or die( "imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, $w_new, $h_new, $curWidth, $curHeight" );
        return $img_out;
    }
    //-------------------------------------------------- ENOUGH MEMORY ?
    private static function enoughmem( $x, $y ) {
        $MAXMEMy = 32 * 1024 * 1024;
        return ( $x * $y * 3 * 1.7 < $MAXMEMy - memory_get_usage() );
    }

} // end class uploadProcessDire
?>