<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Provides an API layer over the system api data
 */
class TemplatingLayer
{
    // Private Variables
    private $_Template = NULL;

    /**
     * Creates a new template instance
     * @param String $dir       The directory where the file resides
     * @param String $file      The template file to parse
     */
    public function __construct($dir, $file)
    {
        $this->_Template = new Template($dir);
        $this->_Template->set_file(array('index' => $file));
    }

    // Public Methods

    /**
     * Sets a variable in the template file with a dynamic value
     * @param String $variable      The variable to set
     * @param String $value         The value to set
     */
    public function set_var($variable, $value)
    {   
        $this->_Template->set_var($variable, $value);
    }

    /**
     * Parses the template and returns the parsed output
     *
     * @return  String      The parsed data
     */
    public function parse_output()
    {
        // Parse the output and return it
        $this->_Template->parse('output', 'index');
        return $this->_Template->finish($this->_Template->get_var('output'));
    }
}
?>
