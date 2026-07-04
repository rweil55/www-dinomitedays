<?php
/*		Freewheeling Easy Mapping Application
 *		A collection of routines fto extract and parse information from  page
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 * function loadBufferWithFile( $file )
 *      move contents of file to $buffer for later extraction
 *      throw error if file not available or other problems
 *      returns number of characters loaded
 * function showBuffer($numBefore, $numAfter): string
 *     returns a string showing the buffer with position marked, and numBefore and numAfter characters on either side
 * function findStringInBuffer($lookFor)
 *     set position to location of lookFor in buffer
 *      return true is found, false if not found
 * function FindStartOfTagInBuffer($tag)
 *     set position to location of tag, ie "<" in buffer
 *      return true is found, false if not found
 * function FindEndOfTagInBuffer($tag)
 *      set position to location of ">" in buffer, after the tag*
 *      return true is found, false if not found
 * function FindTagInBuffer($tag)
 *    set position to beginning of tag in buffer
 * function DiscardTo( $lookFor )
 *		edit $buffer and remove all characters before 'lookFor'
 *		throw error if lookFor not found
 * function discardAll(string $lookFor)
 *      remove from the buffer all characters that match lookFor
 *      resets the position to the start of the buffer
 * function extractTo(string $lookFor)
 *		returns from the start of buffer,upto, not including, lookFor
 *		throw error if lookFor not found
 *		returns array($msgTemp, found string)
 * function extractToEmptyLine()
 *		returns from the start of buffer,upto, not including, first empty line
 * function removeTags( string $text )
 * 		removes html tags from $text
 *		returns ($msg, cleaned out $text)
 * function findHref($text)
 *       **** Not implemented  ****
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
    private $buffer = "";       // data to be parsed
    private $position = -1;     // current pointer in buffer for parsing,
    // all search functions start at this position, and set this to the location of the found item
    // all functions that search buffer should set this to the location of the found item
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


    public function loadBufferWithFile(string $filename, bool $use_include_path = false): int
    {
        $this->buffer = file_get_contents($filename, $use_include_path);
        $sizeBuffer = strlen($this->buffer);
        $this->position = 0;
        return $sizeBuffer;;
    }
    public function loadBufferWithString(string $string): bool
    {
        $this->buffer = $string;
        $this->position = 0;
        return true;
    }
    public function set_position(int $position)
    {
        if ($position < 0)
            throw new Exception("position $position is less than 0");
        if ($position >= strlen($this->buffer))
            throw new Exception("position $position is at or beyond end of buffer " . strlen($this->buffer));
        $this->position = $position;
        return true;
    }
    public function findStringInBuffer(string $lookFor)
    {
        if (empty($lookFor))
            throw new Exception("lookFor is empty");
        if (empty($this->buffer))
            throw new Exception("buffer is empty");
        $tempPosition = strpos($this->buffer, $lookFor, $this->position);
        if (false === $tempPosition)
            return false;
        $this->position = $tempPosition;
        return true;
    }
    public function FindStartOfTagInBuffer(string $tag)
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
    public function FindEndOfTagInBuffer(string $tag)
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
    public function discardTo(string $lookFor)
    {
        self::validateBuffer($lookFor);
        $this->buffer = substr($this->buffer, $this->position);
        return true;
    }
    public function discardAll(string $lookFor)
    {    //     remove from the buffer all characters that match lookFor
        //      resets the position to the start of the buffer
        self::validateBuffer($lookFor);
        str_replace($lookFor, "", $this->buffer);
        $this->position = 0;
        return true;
    }

    public function extractTo(string $lookFor)
    {
        $msg = "";
        global $eol, $errorBeg, $errorEnd;
        self::validateBuffer($lookFor);
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

    public  function removeTags(string $text)
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
    private static function findHref($text)
    {
        throw new Exception("findHref not implemented yet");
    }


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
    public function showBuffer(int $numBefore, int $numAfter): string
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

    private function validateBuffer(...$things)
    {
        if (empty($lookFor))
            throw new Exception("lookFor is empty");
        if (empty($this->buffer))
            throw new Exception("buffer is empty");
        return true;
    }  // end function validateBuffer
} // end class
