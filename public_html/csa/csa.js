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

/**
 * Displays the data in an alert
 * @param   id      The ID of the container holding the data
 */
function containerAlert(id)
{
    alert(document.getElementById(id).innerHTML);
};
