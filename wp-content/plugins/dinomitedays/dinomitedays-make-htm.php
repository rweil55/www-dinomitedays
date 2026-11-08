<?php
//
class dinomitedays_make_html
{
    const baseDire = "/home/pillowan/www-dinomitedays";
    const design_images_dire = self::baseDire . "/designs/images";


    public static function findRelated(string $dino, bool $withDefaults = true)
    {
        // returns a list of filename that are sub pictures for a dino in the designs/images directory.
        global $eol, $errorBeg, $errorEnd;
        $debug = false;
        $dire = self::baseDire . "/designs/images";
        $numChars = strlen($dino);
        $list = array();
        foreach (new DirectoryIterator($dire) as $fileInfo) {
            if ($fileInfo->isDot())
                continue;
            $entry = $fileInfo->getFilename();
            $extension = pathinfo($entry, PATHINFO_EXTENSION);
            if (strncasecmp($dino, $entry, $numChars) != 0)
                continue;   // only want one that match the dino name at the start of the file name
            if (strcasecmp($extension, "jpg") != 0 && strcasecmp($extension, "png") != 0)
                continue;   // only want jpg or png files
            $list["$entry"] = 1;
            //   print "findRelated: looking at " . $fileInfo->getFilename() . $eol;
        }
        ksort($list);

        return $list;
    } // end findRelated
} // end of class
