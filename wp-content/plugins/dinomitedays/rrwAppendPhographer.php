<?php

class AppendPhotographer {


    public static function makeImages( $sourceFile, $photographer,
        $width, $widthThumb, $debug = false ) {
        /* 
        Assume that the max height limit is handler by the caller 
        create two file from the original
            1)  a) resize to $width 
                b) append bottom botder with "photo by photragrapher"
                c) add _cr to filename and place in same direcory
            2)  a) resize to $widthThumb
                b) add _tmb to filename and plave in same directory
        $debug if odd trace file nameing comvensins
        $debug if mod 2= 1 trace image creation
        Throw errors for any problems
        Return any messages
        does not change the exif data. 
        */
        $eol = "<br />";
        $errorBeg = "$eol<style 'text-color:red' >";
        $errorEnd = "</style> $eol";
        global $photoUrl, $photoPath, $thumbUrl, $thumbPath, $highresUrl, $highresPath;
        //      creates the _cr version with bottom line credit of photographer
        //      creates the thumbnail version
        //      moves to the high_resoltion directory
        $msg = "";

        try {
            if ( false === $debug )
                $debug = rrwPara::Integer( "makeimage" );
            if ( false !== $debug ) {
                $debugImageWork = $debug | 2;
                $debug = $debug | 1;
            } else {
                $debugImageWork = false;
                $debug = false;
            }
            $debugImageWork = true;
            $debug = true;
            if ( $debug )$msg .= "makeImages( $sourceFile, $photographer ) $eol";
            $h_botWhite = 20; #	height of the white bar at the bottom f
            $fontSize = 12; #	height of the photographer text
            $fontfile = "arial.ttf";
            $fontDire = "/home/pillowan/www-shaw-weil-pictures/wp-content/plugins/roys-picture-processng";
            $fontfile = "$fontDire/mvboli.ttf";

            $iiSlash = strrpos( $sourceFile, "." );
            $direSource = substr( $sourceFile, 0, $iiSlash );
            $fullfileThumb = "$direSource" . "_tmb.jpg";
            $fullFilePhoto = "$direSource" . "_cr.jpg";
            if ( $debug ) {
                $msg .= "direSource = $direSource $eol";
                $msg .= "fullfileThumb : $fullfileThumb $eol";
                $msg .= "fullFilePhoto : $fullFilePhoto $eol";
            }
            //  ------------------------------ make sure the font file is there
            if ( !file_exists( $fontfile ) ) {
                $msg .= "bad font $fontfile ";
                throw new Exception( "$msg $errorBeg E#812 Problems with 
                                the font file $errorEnd" );
            }
            // create new dimensions, keeping aspect ratio
            $imageInfo = getimagesize( $sourceFile );
            $w_src = $imageInfo[ 0 ];
            $h_src = $imageInfo[ 1 ];

            if ( $h_src == 0 || $w_src == 0 ) {
                throw new Exception( " $errorBeg width is $w_src or Height is $h_src zero $errorEnd" );
            }
            //  ------------------------------------------- resize thumbnail
            $aspect = $h_src / $w_src; // maintain the aspect ration
            $w_tmb = $widthThumb;
            $h_tmb = $w_tmb * $aspect;
            if ( $debug )$msg .= "resizetmb ($w_src, $h_src, $w_tmb, $h_tmb)$eol ";
            $img_src = self::loadtheimageintocore( $sourceFile );
            $img_tmb =
                self::resizeImage( $img_src, $w_tmb, $h_tmb );
            if ( $debug )$msg .= "Back from resize saving to $fullfileThumb $eol";
            if ( !imagejpeg( $img_tmb, $fullfileThumb ) ) //  save new image
                throw new Exception( " E#816 imagejpeg(img_tmb, 
                                    $fullfileThumb ) failed " );
            if ( $debugImageWork )$msg .= " thumbnail saved to 
                                                $fullfileThumb $eol";
            imagedestroy( $img_tmb ); // free memory
            // --------------------------------------------- resize copyright
            if ( $w_src != $width ) {
                $w_cr = $width;
                $h_cr = $h_src * $aspect;
                $img_src = self::loadtheimageintocore( $sourceFile );
                $img_copyright =
                    self::resizeImage( $img_src, $w_cr, $h_cr );
                imagedestroy( $img_src ); // free memory
                if ( $debugImageWork )$msg .= "aspect = $aspect image 
                                resized $w_src X $h_src to $w_cr X $h_cr $eol";

            } else {
                $img_copyright = self::loadtheimageintocore( $sourceFile );;
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
                $copyrightMsg = "Photo by $photographer";
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
            } // end of drawing photographer name
        } // end try
        catch ( Exception $ex ) {
            $msg .= $errorBeg . $ex->getMessage() . $errorEnd;
        }
        return $msg;
    } // end function MakeImakeImages  

    private static function loadtheimageintocore( $sourceFile ) {

        # load the image into core
        $im_src = new Imagick();
        $mime_type = mime_content_type( $sourceFile );
        switch ( $mime_type ) {
            case "image/gif": //   gif -> jpg
                $img_src = imagecreatefromgif( $sourceFile );
                break;
            case "image/jpg": //   jpeg -> jpg
            case "image/jpeg": //   jpeg -> jpg
                $img_src = imagecreatefromjpeg( $sourceFile );
                break;
            case "image/png": //   png -> jpg
                $img_src = imagecreatefrompng( $sourceFile );
                break;
            default:
                throw new Exception( " $errorBeg File '$sourceFile' is
                    mime_type, only GIF, JPG, jpeg or PNG are allowed 
                    $errorEnd" );
        }
        return $img_src;
    } // end loadtheimageintocore
    
    private static function resizeImage( $img_in, $w_new ) {
        global $eol;

        $debugResize = true;
   
        $imgout = imagescale(  $img_in, $w_new);
        if ( false === $imgout )
            throw new Exception( "E#651 failure in resize to $w_new ");
        return $imgout;



        /* -- resample rather than scale
        $img_out = imagecreatetruecolor( $w_new, $h_new )
        or die( "imagecreatetruecolor($w_new, $h_new)" );
        imagecopyresampled( $img_out, $img_in, 0, 0, 0, 0, $w_new, $h_new, $curWidth, $curHeight )
        or die( "imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, $w_new, $h_new, $curWidth, $curHeight" );
        return $img_out;
        */
    }  // end resize image

} // end class uploadProcessDire
?>