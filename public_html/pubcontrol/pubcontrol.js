

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

/**
 * Checks the validity of form entries for modifying (create / update) a feed
 */
ValidityCheck.modifyFeed = function()
{
    try
    {
        // Grab each value
        var name = document.getElementById("GEEKLOG_PUBFNAME").value;
        var summary = document.getElementById("GEEKLOG_PUBFSUMMARY").value;

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

/**
 * Checks the validity of form entries for modifying (create / update) a security group
 */
ValidityCheck.modifySGroup = function()
{
    try
    {
        // Grab each value
        var name = document.getElementById("GEEKLOG_PUBSNAME").value;
        var summary = document.getElementById("GEEKLOG_PUBSSUMMARY").value;

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


/**
 * Asks a user if they would like to delete the item using the message supplied
 *
 * @param   msg      The message to display
 * @param   ontrue   The location to go on success
 * @param   onfalse  The location to go on failure, defaults to none
 */
function deleteItem(msg, ontrue, onfalse)
{
    var answer = confirm(msg);

    if(answer) {
        window.location = ontrue;
    }
    else {
        if(onfalse) {
            window.location = onfalse;
        }
    }
};