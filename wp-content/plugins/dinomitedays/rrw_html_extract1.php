<?php
/*		Freewheeling Easy Mapping Application
 *		A collection of routines fto extract and parse information from  page
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 * function loadBufferWithFile( $file )
 *      move contents of file to $buffer for later extraction
 *      throw error if file not available or other problems
 *      returns number of characters loaded
 * function findStringInBuffer($lookFor)
 *     set position to location of lookFor in buffer
 * function FindTagInBuffer($tag)
 *    set position to beginning of tag in buffer
 * function DiscardTo( $lookFor )
 *		edit $buffer and remove all characters before 'lookFor'
 *		throw error if lookFor not found
 * function extractTo( $lookFor )
 *		returns from the start of buffer,upto, not including, lookFor
 *		throw error if lookFor not found
 *		returns array($msgTemp, found string)
 * function removeTags( $text )
 * 		removes html tags from $text
 *		returns ($msg, cleaned out $text)
 * function findHref($text)
 *		returns an array of URLs associated with a href
 *		return ($msg, array of URLs);
 * function extractToEmptyLine( )
 *		===  self::extract("\n\n");
 * function recursiveDirectoryIterator ($directory, $files
 *		return a list of all files under directory
 */
require_once "rrw_util_inc.php";

class rrwExtractHtml
{
    private $buffer = "";
    private $position = -1;
    public $debugParse = false;
    function __construct($file = "")
    {
        if (empty($file)) {
            $this->buffer = "";
            $this->position = -1;
            return;
        }
        if (is_file($file)) {
            $this->buffer = file_get_contents($file);
            $this->position = 0;
            return;
        }
        $buffer = "";
        $this->position = -1;
        throw new Exception("$file is not a file. Now What?");
    } // end function __construct


    public function loadBufferWithFile($file)
    {
        $this->buffer = file_get_contents($file);
        $sizeBuffer = strlen($this->buffer);
        $this->position = 0;
        return "# buffer loaded $file, with $sizeBuffer  characters";
    }
    public function loadBufferWithString($string)
    {
        $this->buffer = $string;
        $sizeBuffer = strlen($this->buffer);
        $this->position = 0;
        return "$sizeBuffer  characters";
    }
    public function findStringInBuffer($lookFor)
    {
        if ($this->position >= strlen($this->buffer))
            throw new Exception("position " . $this->position . " is at or beyond end of buffer " . strlen($this->buffer));
        if (empty($lookFor))
            throw new Exception("lookFor is empty");
        if (empty($this->buffer))
            throw new Exception("buffer is empty");
        $this->position = strpos($this->buffer, $lookFor, $this->position);
        if (false === $this->position)
            throw new Exception("did not find '$lookFor' in remaining buffer");
        return true;
    }
    public function FindStartOfTagInBuffer($tag)
    {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        if (false === $this->findStringInBuffer($tag))
            throw new Exception("$msg $errorBeg E#934 did not find $tag in remaining buffer $errorEnd");
        $this->position - strrpos($this->buffer, "<", $this->position);
        if (false === $this->position)
            throw new Exception("$msg $errorBeg E#933 did not find beginning of $tag in preceding buffer $errorEnd");
        return true;
    }
    public function FindEndOfTagInBuffer($tag)
    {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        $debugEnd = false;
        if (false === $this->findStringInBuffer($tag))
            throw new Exception("$msg $errorBeg E#934 did not find $tag in remaining buffer $errorEnd");
        if ($debugEnd) print "FindEndOfTagInBuffer: after look" . $this->showBuffer(50, 50);
        $this->position = strpos($this->buffer, ">", $this->position);
        if ($debugEnd) print "FindEndOfTagInBuffer: after carrot" . $this->showBuffer(50, 50);
        if (false === $this->position)
            throw new Exception("$msg $errorBeg E#933 did not find beginning of $tag in preceding buffer $errorEnd");
        return true;
    }
    public function discardTo()
    {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        if ($this->position < 0)
            throw new Exception("$msg $errorBeg E#932 position not set in buffer $errorEnd");
        $this->buffer = substr($this->buffer, $this->position);
        return true;
    }
    public function extractTo($lookFor)
    {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        if ($this->position < 0)
            throw new Exception("$msg $errorBeg E#932 position not set in buffer $errorEnd");
        $startPosition = $this->position;
        if (false === $this->findStringInBuffer($lookFor))
            throw new Exception("$msg $errorBeg E#917 did not find $lookFor in remaining buffer $errorEnd");
        $output = substr($this->buffer, $startPosition, $this->position - $startPosition);   // get the extraction
        return $output;
    }

    public  function extractToEmptyLine()
    {

        return $this->extractTo("\n\n");
    }

    public  function removeTags($text)
    {
        global $eol, $errorBeg, $errorEnd;
        $msg = "";

        $cnt = 0;
        $iiLookEnd = 0;
        while (1) {
            $cnt++;
            if ($cnt > 50)
                throw new Exception("$msg $errorBeg E#905 to many tags found $errorEnd" . htmlspecialchars($text) . $eol);
            if ($iiLookEnd > strlen($text))
                break;
            $iiLookBeg = strpos($text, "<", $iiLookEnd);
            if (false === $iiLookBeg)
                break;
            $iiLookEnd = strpos($text, ">", $iiLookBeg);
            if (substr($text, $iiLookBeg, 4) == "</p>")
                continue; // leave </p> there
            if (substr($text, $iiLookBeg, 2) == "<p") {
                $iiLookBeg = $iiLookBeg + 2; // clean out <p .... >
                $iiLookEnd--;
            }

            $text = substr($text, 0, $iiLookBeg) . substr($text, $iiLookEnd + 1);
            $numRemoved = $iiLookEnd - $iiLookBeg;
            $iiLookEnd = $iiLookEnd - $numRemoved;
        }
        $text = trim($text);
        if (substr($text, 0, 4) == "</p>")
            $text = substr($text, 5); // begins with </p>
        if (substr($text, -3) == "<p>")
            $text = substr($text, 0, strlen($text) - 3); // ends with <p>
        return array($msg, $text);
    }
    private static function findHref($text) {}


    public static function recursiveDirectoryIterator($directory = null, $files = array())
    {
        if (!is_dir($directory))
            throw new Exception("'$directory' is not a directory. Now What?");

        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $info) {
            $filename = $info->__toString();
            if ($info->isFile()) {
                $files["$directory/$filename"] = "$directory/$filename";
            } elseif ($info->isDot()) {
                continue;
            } elseif ($info->isDir()) {
                $files = self::recursiveDirectoryIterator("$directory/$filename", $files);
            } else {
                rrwUtil::print_r($info, true, "a info is not a file, directory or a dot");
            }
        }
        return $files;
    }
    public function showBuffer($numBefore, $numAfter): string
    {
        $msg = "";
        global $eol;
        $startPos = max(0, $this->position - $numBefore);
        $endPos = min($this->position + $numAfter, strlen($this->buffer));
        $length1 = $this->position - $startPos;
        $msg .= str_repeat("V", 100) . "$startPos, $this->position,  $endPos, length = $length1 $eol";
        $msg .= htmlspecialchars(substr($this->buffer, $startPos, $length1)) . $eol;
        $msg .= str_repeat("-", 40) . $eol;
        $msg .= htmlspecialchars(substr($this->buffer, $this->position, $endPos - $this->position)) . $eol;
        $msg .= str_repeat("V", 100) . $eol;
        return $msg;
    }  // end function showBuffer
} // end class
