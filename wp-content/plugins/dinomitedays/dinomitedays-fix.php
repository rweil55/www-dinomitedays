<?php

class dinomitedys_fix
{
    const rrw_dinomites = "wpprrj_00rrwdinos";
    const baseDire = "/home/pillowan/www-dinomitedays";
    const design_images_dire = self::baseDire . "/designs/images";


    public static function fix($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        global $homePath;
        $homePath = substr(ABSPATH, 0, -1);
        $msg = "";
        ini_set("display_errors", true);
        error_reporting(E_ALL | E_STRICT);

        $options = "";
        $task = rrwUtil::fetchparameterString("task");
        $query = rrwUtil::fetchparameterString("q");
        if (!empty($query)) {
            $msg .= self::SearchForQuery($query);
            return $msg;
        }
        if (rrwUtil::NotallowedToEdit(" fix things", "any", true))
            return "$msg not allowed to fix things";
        $msg .= "task of $task $eol ";
        switch ($task) {
            case "deletenew":
                $msg .= self::deleteNew($attr);
                break;
            case "designfooter":
                $msg .= self::designfooter();
                return $msg;
            case "drivingtour":
                $fix = "drivingtour";
                $msg .= self::doFixLoop($fix, $homePath, $options);
                break;
            case "find_filename":
                $fix = "find_filename";
                $msg .= self::doFixLoop($fix, "$homePath/designs", $options);
                break;
            case "http2https":
                $fix = "http2https";
                $msg .= self::doFixLoop($fix, $homePath, $options);
                break;
            case "geocoded":
                $msg .= self::geocoded();
                break;
            case "heads":
                $msg .= self::heads();
                break;
            case "imacro":
                $msg .= self::makeImacro();
                break;
            case "find_images":
                $fix = "find_images";
                $msg .= self::doFixLoop($fix, $homePath, $options);
                break;
            case "findinfofiles":
                return "$msg table has been loaded, therefore nothing to do here $eol";
                /*
                $file = "$homePath/artist.htm";
                $dom = file_get_html($file);
                $anchors = $dom->find("a");
                $cnt = 300;
                foreach ($anchors as $anchor) {
                    $cnt++;
                    $name = $anchor->href;
                    if ("designs" != substr($name, 0, 7))
                        continue;
                    $fileData = "$homePath/$name";
                    $name = str_replace("designs/", "", $name);
                    $name = str_replace(".htm", "", $name);
                    $buffer = file_get_contents($fileData);
                    $iidip = strpos($buffer, "/dip");
                    if (false !== $iidip)
                        $num = substr($buffer, $iidip + 4, 2);
                    else
                        $num = $cnt;
                    $num = str_replace(".", " ", $num);
                    $msg .= " $num - $name $eol";
                    $update = array("infofilename" => $name, "id" => $num);
                    $msg .= $wpdb->insert(
                        self::rrw_dinomites,
                        $update
                    ) . $eol;
                }
                break;
                */
            case "findnew":
                $msg .= self::findNew($attr);
                break;
            case "findpictures":
                $msg .= self::findPictures($attr);
                break;
            case "missing_sm":
                $msg .= self::missing_sm($attr);
                break;
            case "phototogs":
                $msg .= DisplayPhotographers::Display($attr);
                break;
            case "rejectdesginimage":
                $msg .= self::rejectDesginImage();
                return $msg;
            case "renamenewdino":
                $msg .= self::renameNewDino();
                break;
            case "replacefooter":
                $dir = "$homePath/designs";
                $fix = "replacefooter";
                $msg .= self::doFixLoop($fix, $dir, $options);
                break;
            case "test":
                $msg .= "<img src='file://P:/digipix-trips/lake-pleasant/P8070586-adj-1067x800.jpg' width='768px'>
                <img src='http://127.0.0.1/validate/success.png' />
                    test file";
                return $msg;
                break;
            case "unlink":
                $msg .= self::unlink();
                return $msg;
            default:
                $msg .= "
<a href='/fixit/?task=designfooter' >Update the footers</a> - currently only workd on the the 200 detail pages</a>$eol
<a href='https://edit.shaw-weil.com/make-dino-map-files/' > Update the map</a>$eol
<a href='/fixit/?task=phototogs' > Update the photographer list</a>$eol
<a href='/fixit/?task=test' >Some random test</a> of one off code </a>$eol
<a href='/fixit/?task=missing_sm' >Create am _sm </a>file if not one there$eol
<a href='/fixit/?task=find_images' >given a dino </a>find all images$eol
<a href='/fixit/?task=imacro' >make 100</a> imacro commands $eol
<a href='/fixit/?task=heads' >generate the missing </a>'head' images $eol
<a href='/fixit/?task=findnew' >locate unprocessed -new files </a></a>$eol
<a href='/fixit/?task=http2https' >Change http: to </a>https: and other                         mechanical actions</a>$eol
<a href='/fixit/?task=replacefooter' >update the footers </a>$eol
<a href='/last_seen?lastorkey=key' > list by keyid/number </a>$eol
<strong> Database wp541 </strong>$eol
<strong> obsolete </strong>$eol
<a href='/fixit/?task=geocoded' >read csv geocode </a>file, set database lat,lng </a>$eol
<a href='/fixit/?task=drivingtour' >update the driving tour</a> links</a>$eol
$eol $eol
";
                $msg .= self::SearchForQuery("");
                break;
        } // end switch     

        return $msg;
    } // end function fixit
    //
    private static function findPictures($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";
        $dire = self::baseDire;
        for ($iiPicNum = 1; $iiPicNum < 6; $iiPicNum++) {
            $file = "$dire/pictures_$iiPicNum.htm";
            $fp = fopen($file, "r");
            if (false === $fp) {
                $msg .= " $file not found $eol";
                continue;
            }
            $msg .= str_repeat("x", 100) . "found  $file $eol";

            $cnt = 0;
            while ($line = fgets($fp)) {
                $cnt++;
                $line = trim($line);
                if (empty($line))
                    continue;
                if (strlen($line) < 50)
                    continue;
                if (substr($line, 0, 14) == '<td width="130') {
                    $msg .= htmlspecialchars($line) . $eol;
                    $fileName = self::FindText($line, "designs/", '"');
                    $logoName = self::FindText($line, "graphics/", '"');
                    $dino = self::FindText($line, 'sans-serif">', "<");
                    if (empty($dino)) {
                        $dino = self::FindText($line, 'alt="', '"');
                    }
                    $designname = str_replace(".htm", "", $fileName);
                    $sql = "select * from " . self::rrw_dinomites . " where name = '$dino'  and filename = '$designname' ";
                    $recs = $wpdb->get_results($sql, ARRAY_A);
                    if ($wpdb->num_rows != 1)
                        $msg .= "$errorBeg E#1377 Did not find (" . $wpdb->num_rows . ") a dinosour for $errorEnd
                            $sql $eol";
                    $set = array("logoFileName" => "$logoName");
                    $which = array("name" => "$dino");
                    if (empty($recs[0]["logoFileName"])) {
                        $recCnt = $wpdb->update(self::rrw_dinomites, $set, $which);
                        if (1 != $recCnt)
                            $msg .= "$errorBeg E#1378 Did not find a dinosour for $errorEnd
                            $sql $eol";
                    } else {
                        $recCnt = "previously updated ";
                    } // end if empty
                    $msg .= " <strong>$fileName -- $logoName -- $dino </strong> updater $recCnt $eol";
                }
            } // end while
            fclose($fp);
            $msg .= str_repeat("x", 100) . "end  $file with $cnt lines $eol";
        } // end for pictures;
        return $msg;
    }
    private static function FindText(&$line, $search, $term)
    {
        global $eol, $errorBeg, $errorEnd;
        $iiDes = strpos($line, $search);
        $searchlen = strlen($search);
        if (false === $iiDes)
            return "";
        $iiEnd = strpos($line, $term, $iiDes + $searchlen);
        $Dinoname = substr($line, $iiDes + $searchlen, $iiEnd - $iiDes - $searchlen);
        $Dinoname = str_replace('"', "", $Dinoname);
        $line = substr($line, $iiEnd);
        return $Dinoname;
    }
    private static function deletenew($attr)
    {
        // delete a -new file
        global $eol, $errorBeg, $errorEnd;
        global $homePath;
        $msg = "";

        $dino = rrwPara::String("dino");
        $newPath = "designs/";
        $filenameFull = "$homePath/$newPath/$dino-new.htm";
        $result = unlink($filenameFull);
        if ($result)
            $msg .= "$filenameFull succefull deleted $eol";
        else
            $msg .= "$errorBeg $filenameFull delete failed $errorEnd";
        $msg .= "<a href='/fix?task=findnew' > locate unprocessed -new files </a>$eol";
        return $msg;
    } // end deletenew
    //
    private static function replaceFooter(&$buffer)
    {
        global $eol, $errorBeg, $errorEnd;
        global $footer;
        $msg = "";
        $debugFooter = false;

        if ($debugFooter) $msg .= "----------------- #1 " . htmlspecialchars($buffer) . "$eol ---------------- $1 $eol";
        $pluginDire = "/wp-content/plugins/dinomitedays";
        if (empty($footer))
            $footer = file_get_contents(self::baseDire .
                "/$pluginDire/footer_dino.php");
        // addin call to style sheet
        $buffer = str_replace("dinomitedasys", "dinomitedays", $buffer);
        $buffer = str_replace("freewheelingdays", "freewheelingeasy", $buffer);
        if (false == strpos($buffer, "dinomitedays.css")) {
            $iiHead = strpos($buffer, "</head");
            $buffer = substr($buffer, 0, $iiHead) .
                "\n<link rel='stylesheet' id='dinomitedays-style-css'  
                      href='https://dinomitedays.org$pluginDire/dinomitedays.css' media='all' />\n" .
                substr($buffer, $iiHead);
        }
        if ($debugFooter) $msg .= "----------------- #2 " . htmlspecialchars($buffer) . "$eol ---------------- $2 $eol";
        $buffer = str_replace("//wp-content", "/wp-content", $buffer);
        $buffer = str_replace("dinomitedasys", "dinomitedays", $buffer);
        // remove inline styles
        for ($ii = 0; $ii < 3; $ii++) {
            $iiStyle = strpos($buffer, "<style");
            if ($iiStyle !== false) {
                $iiEndStyle = strpos($buffer, "</style>", $iiStyle);
                $iiEndStyle = strpos($buffer, "</style>", $iiEndStyle + 2);
                $buffer = substr($buffer, 0, $iiStyle) .
                    substr($buffer, $iiEndStyle + 8);
            }
        }
        if ($debugFooter) $msg .= "----------------- #3 " . htmlspecialchars($buffer) . "$eol ---------------- $3 $eol";

        // replace the footer
        $iiDiv = strpos($buffer, '<div id="dinofooter"');
        if ($debugFooter) $msg .= "in replace footer:div start = $iiDiv $eol";
        if (false !== $iiDiv) {
            // replace it
            $iienddiv = strpos($buffer, "</div>", $iiDiv);
            if ($debugFooter) $msg .= "in replace footer:div end at  = $iienddiv $eol";
            $iienddiv = strpos($buffer, "</div>", $iienddiv + 2);
            $buffer = substr($buffer, 0, $iiDiv) . $footer .
                substr($buffer, $iienddiv + 6);
            $msg .= "footer replaced $eol";
        } else {
            $msg .= "$errorBeg E#1364 no footer found $errorEnd";
            $msg .= htmlspecialchars($buffer);
        }
        return $msg;
    }
    /*
        private static function tryDomDocument() {

            $file = "/home/pillowan//www-dinomitedays/designs/stanford.htm";

            $dom = new DomDocument();
            $buffer = file_get_contents( $file );
            $check = $dom->loadHTML( $buffer );
            if ( $check )
                $msg .= "good load $eol";
            else
                $msg = "$errorBeg E#xxx load of dom sodument failed $errorEnd";
            $div = $dom->getElementById( 'dinofooter' );
            print "<pre>";
            $cnt++;
            print "---------------------------------  $eol";
            var_dump( $div );
            print "</pre>";
            return $msg;
        }
    */
    private static function designfooter()
    {
        global $eol, $errorBeg, $errorEnd;
        global $homePath;
        $msg = "";

        $diresource = "$homePath/designs";
        $direFinal = "$homePath/designsnew";
        $file = rrwPara::String("file");

        if (!is_dir("$direFinal")) {
            if (mkdir($direFinal)) {
                $msg .= "created directory $direFinal $eol";
            } else {
                $msg .= "$errorBeg E#1351 failed to create directory $direFinal $errorEnd";
                return $msg;
            } // end mkdire
        } // end check for directory and make is neccessary
        $cnt = 0;
        $list = array();
        if (empty($file)) {
            $hd = opendir("$diresource");
            while (false !== ($entry = readdir($hd))) {
                $cnt++;
                if ($cnt > 500)
                    break;
                if (is_dir("$diresource/$entry"))
                    continue;
                if (strpos($entry, "LCK") !== false)
                    continue;
                if (strpos($entry, "new") !== false) {
                    $msg .= "unlink ($diresource/$entry)$eol";
                    unlink("$diresource/$entry");
                    continue;
                }
                array_push($list, $entry);
                $msg .= self::ChangeFooter($entry, "designsnew");
            } // end while ( false !== ( $entry = readdir( $hd ) ) ) 
            fclose($hd);
        } else {
            $msg .= self::ChangeFooter($file, "designsnew");
            // leave list empty
        }

        $cnt = 0;
        ksort($list);
        foreach ($list as $item) {
            $cnt++;
            if (($cnt % 22) == 0)
                $msg .= "$eol $eol $eol";
            $msg .= "URL GOTO=https://dinomitedays.org/designsNew/$item$eol
            WAIT SECONDS=3$eol
            ";
        }
        return $msg;
    }

    public static function changeFooter($filesource, $designsNew)
    {
        global $eol, $errorBeg, $errorEnd;
        global $homePath;
        $msg = "";
        $debug = true;

        $buffer = file_get_contents("$homePath/designs/$filesource");
        if (false === $buffer) {
            $msg .= "$errorBeg E#1360 failed to read $homePath/designs/$filesource $errorEnd";
            return $msg;
        }
        // has cmnh footer been replaced already
        $iiDiv = strpos($buffer, '<div id="dinofooter"');
        if (false !== $iiDiv) {
            // replace new style footer
            if ($debug) $msg .= "replaceing new style footer $eol";
            $buffernew = $buffer;
            //$msg .= "----------------- #5 " . htmlspecialchars($buffer) . "$eol ---------------- $5 $eol";

            $msg .= self::replaceFooter($buffernew);
        } else {
            // find old style footer
            if ($debug) $msg .= "replaceing old style header $eol";
            $iifoot = strpos($buffer, ".dinoFotte");
            if (false != $iifoot) {
                $iiClose = $iifoot - 9;
            } else {
                $iiClose = strrpos($buffer, "Close", -1);
                if (false === $iiClose) {
                    $msg .= "$msg $errorBeg E#1349 string 'close' not found in $homePath/$filesource $errorEnd
                        while tryiing to update footer $errorEnd";
                    return $msg;
                }
                $iiClose = $iiClose - 3;
            } // start of therreplace has been found
            $iitr = strpos($buffer, "</tr", $iiClose); // get end
            $msg .= "len is " . strlen($buffer) . "close at $iiClose,  
                        tr at $iitr $eol";
            $footer = file_get_contents(
                "$homePath/wp-content/plugins/dinomitedays/footer_dino.php"
            );
            $buffernew = substr($buffer, 0, $iiClose) . "\n" .
                $footer . "</td>\n" . substr($buffer, $iitr);
        }
        $newfile = "$homePath/$designsNew/$filesource";
        $fpout = fopen($newfile, "w");
        $msg .= fwrite($fpout, $buffernew);
        $msg .= "created $newfile <a href='/$designsNew/$filesource' target='new'> $filesource ></a>$eol";
        return $msg;
    }

    private static function geocoded()
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";

        $febugGeo = true;
        $fp = fopen("/home/pillowan/www-dinomitedays/wp-content/plugins/dinomitedass/Dinolats.csv", "r");
        $line = fgets($fp);
        $line = fgets($fp);
        $msg .= "<table>";
        $cnt = 0;
        while ($line = fgets($fp)) {
            $cnt++;
            if (150 < $cnt)
                break;
            $data = explode(",", $line);
            $key = $data[0];
            $lat = $data[2];
            $long = $data[3];
            $sql = " update " . self::rrw_dinomites .
                " set latitude = $lat, longitude = $long where keyid = $key ";
            $answer = $wpdb->query($sql);
            $msg .= rrwFormat::CellRow($key, $lat, $long, $answer, $sql);
            $wpdb->query($sql);
        } // end while
        $msg .= "</table>";
        $msg .= "processed $cnt items $eol";
        fclose($fp);
        return $msg;
    } // end  geocoded

    private static function heads()
    {
        // --------------------------------------- look at the heads files
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";

        $headImageDire = "designs/graphics";
        $baseDir = "/home/pillowan/www-dinomitedays";
        $inageDir = "$baseDir/$headImageDire";
        $outputDire = "$inageDir/head_calculated"; // same as input
        $urlDire = "$headImageDire/head_calculated";
        // looking in head_clculated to determine file to update.
        $hd = opendir("$inageDir/head_calculated");
        $cnt = 0;
        $list = array();
        while (false !== ($entry = readdir($hd))) {
            $cnt++;
            if ($cnt > 400)
                break;
            if ("head" != substr($entry, 0, 4))
                continue;
            $entry = str_replace("head_", "", $entry);
            $entry = str_replace(".gif", "", $entry);
            $list[$entry] = 1;
        }
        ksort($list);
        $msg .= rrwUtil::print_r($list, true, "Files to rebuidld");

        $cnt = 0;
        foreach ($list as $item => $val) {
            $sql = "select Filename, name, author from " .
                self::rrw_dinomites . " where filename = '$item' ";
            $recnames = $wpdb->get_results($sql, ARRAY_A);
            if (1 != $wpdb->num_rows) {
                $msg .= "$errorBeg E#1331 Did not find a dinosour for $errorEnd
                $sql $eol";
                continue;
            }
            $recname = $recnames[0];
            $cnt++;
            $Filename = $recname["Filename"];
            $name = $recname["name"];
            $author = $recname["author"];
            $file = "/head_$Filename.gif";
            $fileFull = "$inageDir/$file";
            if (file_exists($fileFull) && false) {
                $url = "/$headImageDire/$file ";
            } else {
                $msg .= dinomitedys_fix::makeHead(
                    $outputDire,
                    $urlDire,
                    $file,
                    $name,
                    $author
                );
            }
        } // end directory lok up
        foreach (array(
            "merch" => "Dino Store",
            "media" => "News & Information",
            "owned" => "Purchased by Sponsors",
            "pics" => "Pictures",
            "dug" => "Duquesne",
            "fun" => "Fun Stuff",
            "live" => "Live Auction",
            "lot1" => "Auction Lot 1",
            "lot2" => "Auction Lot 2",
            "lot3" => "Auction Lot 3",
            "raff" => "Raffle",
            "otoole" => "Toyosaurus - Crane",
            "results" => "Results",
            "seek_s" => "Select Sponsor",
            "steg" => "Stegosaurus",
            "web" => "Website Information",
        ) as $file => $name) {
            $file = "head_$file.gif";
            $author = "";
            // needs more code to match the fifferent head of these guys
            $msg .= dinomitedys_fix::makeHead(
                $outputDire,
                $urlDire,
                $file,
                $name,
                $author
            );
        }
        $msg .= "$eol Images output to $outputDire $eol ";
        return $msg;
    }

    private static function makeHead($outputDire, $urlDire, $filename, $name, $author)
    {
        global $eol, $errorBeg, $errorEnd;
        global $counting;
        $msg = "";

        $msg .= " $eol $filename, $name, $author $eol$eol ";
        $fileFull = "$outputDire/$filename";
        $url = "https://dinomitedays.org/$urlDire/$filename ";
        $image = new Imagick();

        $draw1 = new ImagickDraw();
        $draw1->setFontSize(18);
        //         $fillcolor = new ImagickPixel( "rgb( 255, 255, 0" ) ;
        //       $draw1->setFillColor( $fillcolor );
        $draw1->setStrokeWidth(5);
        $draw1->setGravity(Imagick::GRAVITY_WEST);

        $bgColor = new ImagickPixel("#f8ac05"); //"#eb9909" );
        $image->newImage(551, 58, $bgColor);
        $image->annotateImage($draw1, 10, 0, 0, $name);
        $image->setImageFormat('gif');
        $image->writeImage($fileFull);
        $size = getimagesize("$fileFull");
        $msg .= $size[0] . " x " . $size[1] . $eol;
        $msg .= "<img src='$url' /></a>$eol\n";

        if (empty($counting)) {
            $counting = 0;
            $msg .= "<img src='/designs/graphics/dip1.gif' /></a>$eol\n"; // for color match

            $width = '600';
            $height = '200';
            $text = 'Rubblewebs';
            $im = new Imagick();

            $draw2 = new ImagickDraw();
            $draw2->setFontSize(96);
            $fillcolor = new ImagickPixel("rgb(255,0,0)");
            $draw2->setFillColor($fillcolor);
            $draw2->setGravity(Imagick::GRAVITY_CENTER);

            $bgcolor = new ImagickPixel("black");
            $im->newImage($width, $height, $bgcolor);
            $im->annotateImage($draw2, 1, 0, 0, $text);
            $im->setImageFormat("gif");
            $im->writeImage("$outputDire/text.gif");
        }
        $counting++;
        return $msg;
    }

    private static function rejectDesginImage()
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $debugreject = true;

        $imagename = rrwPara::String("file");
        $siteDir = "/home/pillowan/www-dinomitedays/";
        $imagePath = "designs/images";
        $fileName = self::baseDire . "/$imagePath/$imagename";
        $filenameNew = self::baseDire . "/$imagePath-rejected/$imagename";
        if (file_exists($fileName)) {
            rename($fileName, $filenameNew);
            $msg .= " $fileName rejected $eol";
        } else {
            $msg .= "$errorBeg E#1376 file '$fileName' not found to reject $errorEnd";
        }
        $iiSlash = strrpos($fileName, "/");
        $dino = substr($fileName, $iiSlash + 1);
        $iiUnder = strpos($dino, "_");
        $dino = substr($dino, 0, $iiUnder);
        if (true == $debugreject)
            throw new Exception("$msg $errorBeg dinomitedys_make_html::UpdateImages
                    ( $dino );$errorEnd ");

        $msg .= dinomitedys_make_html::UpdateImages($dino);
        $msg .= "<a href='/upload?dinofile=$dino' > Display images </a> $eol";
        return $msg;
    }

    private static function renameNewDino()
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $dino = rrwPara::String("dino");
        $siteDir = "/home/pillowan/www-dinomitedays";
        $htmlPath = "designs";
        $fileNameNew = "$siteDir/$htmlPath/$dino-new.htm";
        $fileNameOld = "$siteDir/$htmlPath/$dino.htm";
        $filenameSave = "$siteDir/wp-content/$dino" . "_" . date("Y-m-d") . ".htm";
        if (!file_exists($fileNameNew))
            throw new Exception("$msg $errorBeg E#1362 file $fileNameNew not exist $errorEnd ");
        if (file_exists($fileNameOld)) {
            $result3 = rename($fileNameOld, $filenameSave);
            if (false === $result3)
                throw new Exception("$msg $errorBeg E#1371 failure of
                            rename( $fileNameOld, $filenameSave ); $errorEnd");
        }
        $result2 = rename($fileNameNew, $fileNameOld);
        if (false === $result2)
            throw new Exception("$msg $errorBeg E#1368 failure of
                            rename( $fileNameNew, $fileNameOld ); $errorEnd");
        $msg .= "<a href='/designs/$dino.htm' > Check out final verion </a> $eol";
        return $msg;
    }

    private static function SearchForQuery($query = "")
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";

        if (empty($query)) {
            $msg .= "
            <form method='get' >
            <input type='text' name='q' id='q' />
            <input type='submit' name='submit value='Enter patial filename for search' />
            ";
            return $msg;
        }
        $sqlTrys = array(
            "name" => "filename",
            "filename" => "name",
        );

        $msg .= "also try: ";
        foreach ($sqlTrys as $name => $filename) {
            $sql = " select $name from " . self::rrw_dinomites . " where $filename like '%$query%'
                order by $name ";
            $recnames = $wpdb->get_results($sql, ARRAY_A);
            foreach ($recnames as $recname) {
                $nameout = $recname["$name"];
                $msg .= "[ <a href='/fix/?q=$nameout' >$nameout </a> ] ";
            }
        }
        $msg .= $eol;
        $msg .= self::doFixLoop("find_related", self::baseDire, $query) . $eol;
        return $msg;
    }
    private static function makeImacro()
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";
        $sql = "select Filename from " . self::rrw_dinomites . " order by Filename ";
        $recnames = $wpdb->get_results($sql, ARRAY_A);
        $cnt = 0;
        foreach ($recnames as $recname) {
            $cnt++;
            $name = $recname["Filename"];
            $msg .= "URL GOTO=https://dinomitedays.org/designs/$name.htm$eol
WAIT SECONDS=4$eol";
            if (0 == ($cnt % 20))
                $msg .= "$eol$eol--------------------------- $cnt $eol$eol";
        }
        return $msg;
    }
    public static function findNew($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";

        $dir = self::baseDire . "/designs";
        $cntFound = $cntRead = 0;
        foreach (new DirectoryIterator($dir) as $item) {
            $cntRead++;
            if (false === strpos($item->getBasename(), "-new."))
                continue;
            $dino = $item->getBasename("-new.htm");
            $msg .= " Current version <a href='/designs/$dino.htm' target='old'>
                    $dino.htm</a>, 
                an  updated verson of <a href='/designs/$dino-new.htm' 
                target='new' > $dino-new.htm is here</a>. 
                Check it out. Do NOT forget to refreash. If okay 
                <a href='/fixit/?task=renamenewdino&dino=$dino' target='new'>
                move to production</a> 
                or <a href='/fix?task=deletenew&dino=$dino' >delete the newer version </a>
                or <a href='/update/?dino=$dino' target='update'> retry the update </a> $eol";;
        }
        $msg .= "$eol found $cntRead dires/files of which 
                    $cntFound were updated $eol";
        return $msg;
    }

    private static function missing_sm($attr)
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdb;
        $msg = "";
        $sql = "select Filename from " . self::rrw_dinomites . " order by Filename ";
        $recnames = $wpdb->get_results($sql, ARRAY_A);
        foreach ($recnames as $recname) {
            $name = $recname["Filename"];
            $filesm = self::design_images_dire . "/$name" . "_sm.jpg";
            $fileBig = self::design_images_dire . "/$name" . ".jpg";
            if (file_exists($filesm))
                continue;
            if (!file_exists($fileBig)) {
                $msg .= "missing $fileBig $eol";
                continue;
            }
            $copy = "copy ($fileBig, $filesm);";
            copy($fileBig, $filesm);
            $msg .= "$copy $eol";
        }
        return $msg;
    }

    private static function doFixLoop($fix, $dir, $options)
    {
        // loop through all the files in $dir recursively and do the task spcieid by $fix
        global $eol, $errorBeg, $errorEnd;
        $debugWhile = false;
        $msg = "";

        if ($debugWhile) $msg = "doFixLoop on directory $dir $eol";

        $iiDays = strpos($dir, "days");
        if ($debugWhile) $msg .= "<h1> fix stuff $fix  - $dir</h1>";

        $http = "https://dinomitedays.org" . substr($dir, $iiDays + 5);

        $handle = opendir("$dir");
        if (!is_resource($handle))
            throw new Exception("$msg E#1373 that is not a directory");
        $cnt = 0;
        $entry = true;
        while (($entry = readdir($handle)) !== false) {
            $cnt++;
            if ($cnt > 2000)
                throw new Exception("$msg E#1348 - $entry Too mnay times $cnt in the while loop $eol");
            if (("." == substr($entry, 0, 1)) || ("wp" == substr($entry, 0, 2)))
                continue;
            if (strpos($entry, "fix") !== false)
                continue; //  go not mess with me
            if (substr($entry, 0, 2) == "wp")
                continue;
            $file = "$dir/$entry";
            $direNew = "$dir" . "_new/";
            $fileNew = "$direNew/$entry"; // make a seperate directory for changes
            if ($debugWhile) $msg .= "$file $eol";
            if (is_dir($file)) {
                $msg .= self::doFixLoop($fix, "$dir/$entry", $options);
                continue;
            }
            $ext = substr($entry, -3);
            $buffer = file_get_contents($file);
            $originalLength = strlen($buffer);
            $replace = false;
            switch ($fix) {
                case "find_related":
                    // display a collectio of files, whose name contains q= 
                    if (false === stripos($entry, $options))
                        break; // next file
                    $ext = substr($entry, -3);
                    if (("gif" != $ext) && ("jpg" != $ext)) {
                        // non image file
                        $msg .= "<a href='$http/$entry' target='nonimage' > 
                        <span style='font-size:16; font-weight:bold'> $file </span></a>$eol";
                    } else {
                        $msg .= "<img src='$http/$entry' width='200px' /> <br>
                        <a href='$http/$entry' target='image' >$http/$entry </a> $eol";
                    }
                    break;

                case "http2https":
                    // changes for the archive/backup version to make itwork
                    $ext = substr($entry, -3);
                    if ("htm" != $ext)
                        continue 2; // next flle

                    $buffer = str_replace('http://carnegie"', 'https://carnegie"', $buffer);
                    $buffer = str_replace("www.CarnegieMNH", "carnegiemnh", $buffer);
                    $buffer = str_replace("https://carnegiemuseums/", "https://carnegiemuseums.org", $buffer);
                    $buffer = str_replace("https://carnegiemnh/", "https://carnegiemnh.org", $buffer);
                    $buffer = str_replace("https://carnegiemnh.org/index.htm", "https://carnegiemnh.org", $buffer);

                    $buffer = str_replace("dinomitedaysauction", "/auction", $buffer);
                    $buffer = str_replace("dinomitedasys", "dinomitedays", $buffer);

                    $len2 = strlen($buffer);
                    $replace = true;
                    break;

                case "find_images":
                    // display a collectio of images
                    $ext = substr($entry, -3);
                    //               $msg .=  "$entry - $ext $eol";
                    if (("gif" != $ext) && ("jpg" != $ext))
                        continue 2; // next flle
                    if (strpos($file, $options) === false)
                        continue 2;
                    $msg .= "<li><img src='$http/$entry' width='200px' /> <br>$http/$entry</li>";
                    continue 2; // next flle
                    break;
                case "drivingtour":
                    // link the driving tour
                    $ext = substr($file, -3);
                    if ("htm" != $ext)
                        continue 2; // next flle
                    if (false !== strpos($buffer, "tour was")) {
                        $msg .= "$errorBeg tour on $file $errorEnd";
                        continue 2;
                        $iiSlash = strrpos($file, "/");
                        $link = "https://dinomitedays.org/" . substr($file, $iiSlash);
                        $msg .= "check [ <a href='$link' target='new' >$file</a> ] $eol";
                        continue 2; // next flle
                    }
                    $msg .= "not $file $$eol";
                    continue 2;
                    $iiLoc = strpos($buffer, "locations of the");
                    if (false === $iiLoc)
                        break;
                    $iidino = strpos($buffer, "dino", $iiLoc);
                    if (false === $iidino)
                        break;
                    $diff = $iidino - $iiLoc;;
                    $msg .= "Diff = $diff  &nbsp; $file $eol";
                    if (68 != $diff)
                        continue 2; // next flle
                    $tour = "<a href='https://carnegiemnh.org/jurassic-days-dino-statue-driving-tour/' 
					> a tour was created. </a>";
                    $step = 10;
                    $buffer = substr($buffer, 0, $iidino + $step) .
                        "However some of the dinosaurs were
					located in 2010, and $tour " . substr($buffer, $iidino + $step);
                    break;
                case "replacefooter":
                    $msg .= self::replaceFooter($buffer);
                    break;
                default:
                    throw new Exception("$msg $errorBeg #E751 no fix selected 
                    $errorEnd");
            } // end switch
            // write the file ifchanges have been made
            $fianlLength = strlen($buffer);
            if ($originalLength != $fianlLength) {
                $msg .= "$file length changed $originalLength != $fianlLength $eol";
                if ($replace)
                    $fp = fopen($file, "w");
                else {
                    if (!is_dir($direNew))
                        mkdir($direNew);
                    $fp = fopen($fileNew, "w");
                }
                $cntWriten = fwrite($fp, $buffer);
                fclose($fp);
                $msg .= "Write $cntWriten bytes of information $eol";
            }
        } // end while
        if ($debugWhile) $msg .= "fix loop finished $eol";
        return $msg;
    } // end function

    private static function setLocation($filename, $address, $year, $latLong)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";

        $fileLoc = "/home/pillowan/www-dinomitedays/designs/$filename";
        $isGood = rrwUtil::fetchparameterBoolean("isgood");
        if ($isGood)
            $fileLocOut = $fileLoc;
        else
            $fileLocOut = $fileLoc . "l";
        $buffer = file_get_contents($fileLoc);

        $iiloc = strpos($buffer, "Location");
        if (false === $iiloc)
            return "$msg $errorBeg E#1366 the word location was not found. $errorEnd";
        $iiloc = $iiloc + 8; // iiloc is hust after the N
        $msg .= "'" . substr($buffer, $iiloc, 2) . "' $eol";
        if (substr($buffer, $iiloc, 2) == " (")
            $iiloc2 = $iiloc + 7;
        else
            $iiloc2 = $iiloc;
        if (!empty($year))
            $buffer = substr($buffer, 0, $iiloc) . " ($year)" . substr($buffer, $iiloc2);

        $iiloc = strpos($buffer, "</font", $iiloc2) + 7;
        $iiloc2 = strpos($buffer, "<br>", $iiloc);
        if (!empty($address)) {
            $buffer = substr($buffer, 0, $iiloc) . " $address" . substr($buffer, $iiloc2);
            $iiloc += strlen($address) + 1;
        }
        if (!empty($latLong)) {
            $link = "<a href='https://www.google.com/maps/place/$latLong' target='new'>$latLong</a>";
            $buffer = substr($buffer, 0, $iiloc) . " $link" . substr($buffer, $iiloc);
        }

        $fp = fopen($fileLocOut, "w");
        fwrite($fp, $buffer);
        $iiSlash = strrpos($fileLocOut, "/");
        $newName = substr($fileLocOut, $iiSlash);
        $msg .= "<a href='https://www.dinomitedays.org/designs/$newName' 
			target='new' > $newName </a>";

        return $msg;
    }
    private static function unlink()
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";
        $siteDir = "/home/pillowan/www-dinomitedays";

        $where = "designs/graphics";
        $dire = "$siteDir/$where";
        $lookfor = "morr";
        $msg .= "looking for '$lookfor' in '$dire'";
        if (!is_dir($dire)) {
            $msg .= "$errorBeg dire not found $dire $errorEnd";
            return $msg;
        }

        foreach (new DirectoryIterator($dire) as $entry) {
            if (false !== strpos($entry, $lookfor)) {
                $msg .= "delete $dire/$entry $eol";
                unlink("$dire/$entry");
            } // end a match
        } // end foreach
        return $msg;
    } // end unlink()
} // end class
