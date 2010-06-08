<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Provides a buffer to write to or to have print out
 */
class BufferedCharacterWriter
{
    // Private Variables
    private $_BufferPrint = false;
    private $_BufferOutput = "";

    // Public methods

    /**
     * Creates the new object in memory
     * @param Boolean $print         Instead of storing in memory, print directly to output
     */
    public function __construct($print=FALSE)
    {
        $this->_BufferPrint = $print;
    }

    /**
     * Returns the output that has been written to the buffer
     *
     * @return  String          The written output
     */
    public function getOutput()
    {
        return $this->_BufferOutput;
    }

    /**
     * Writes the string to the buffer
     * @param String $str       The string to store
     */
    public function write($str)
    {
        if($this->_BufferPrint === TRUE)
        {
            echo $str;
        }
        else
        {
            $this->_BufferOutput .= $str;
        }
    }
}

?>
