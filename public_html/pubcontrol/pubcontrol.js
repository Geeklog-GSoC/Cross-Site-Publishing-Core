

/**
 * Object wrapper for validity checks
 * @class   ValidityCheck
 */
function ValidityCheck()
{
    
};


/**
 * Checks the validity of form entries for modifying (create / update) a group
 */
ValidityCheck.modifyGroup = function()
{
    try
    {
        // Grab each value
        var name = document.getElementById("GEEKLOG_PUBGNAME").value;
        var summary = document.getElementById("GEEKLOG_PUBGSUMMARY").value;
        var type = document.getElementById("GEEKLOG_PUBGTYPE").value;

        if((name == "") || (summary == "")) {
            // Display error
            document.getElementById("GEEKLOG_PUPLOAD_ERRFORM").innerHTML = "Error: Name or Summary not valid";
            return false;
        }

        return true;
    }
    catch(e)
    {
        document.getElementById("GEEKLOG_PUPLOAD_ERRFORM").innerHTML = "Error: Internal JSDOM Error";
        return false;
    }
};