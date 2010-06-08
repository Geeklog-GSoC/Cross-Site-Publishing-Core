<?php
require_once 'simpletest/autorun.php';


class AllTests extends TestSuite
{
    public function __construct()
    {
        $this->TestSuite('All Tests');

        if(($handle = opendir('tests/')) !== FALSE)
        {
            while(false !== ($file = readdir($handle)))
            {
                if( ($file === ".") || ($file === ".."))
                {
                    continue;
                }
                
                $this->addFile("tests/" . $file);
            }

            closedir($handle);
        }
        else
        {
            echo "Error - Cannot open test case directory";
        }

    }
}

?>