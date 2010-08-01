<?php
header("Content-Type: text/plain");

// Will read from the $_CONF files eventually.
// The conf values are for whether the repository supports public and private
echo ( 0 | 0x02 | 0x04);
// Supports both -> 0x02 = public, 0x04 = private
?>