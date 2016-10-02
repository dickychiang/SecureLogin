<?php

/*
 * Does user management.
 * The minimal set of options is the ability to change an existing user's password and to give or revoke administrator rights.
 * The contents of the page is only accessible to users with administrator rights.
 * Other authenticated users get a message informing them they do not have permission to access the page.
 * For unauthenticated users, you can choose any reasonable behaviour.
 *
 */
require_once("process.php");

$USER = new User();
//var_dump($user->authenticated);

?>
<!DOCTYPE html>
<html>
    <head>
        <title> User Management </title>
        <link rel="stylesheet" type="text/css" href="css/style.css"  />
        <script type="text/javascript" src="js/sha1.js"></script>
        <script type="text/javascript" src="js/user.js"></script>
    </head>
    <body>
        <div id="main">
            <h2>
                Hi admin, you have a privilege to change one existing user's password and give or revoke administrator rights. Just simply modify his or her information by click the button of update.
            </h2>

            <span style="color:red">Note that leaving an empty in the field of password or roles would keep its original one.</span>
<?php if($USER->authenticated && $USER->role == "admin" && !$USER->update) { $db_name = $USER->get_all_users(); $i = 0; foreach($db_name as $key=>$value) {   ?>
            <input id="<?php echo $i; ?>" class="cc" name="p[]" type="button" value="<?php echo $value; ?>" />
            <div class="output"></div>
<?php $i++; } } ?>

<?php if($USER->authenticated && $USER->role == "admin" && $USER->update) { ?>
        <h2>
            The information has been updated ! Would you like to continue to <a href="admin.php">update</a> other users or back to <a href="main.php">Main page</a> ?
        </h2>
<?php } ?>

<?php if($USER->authenticated && $USER->role == "user") { ?>
        <h2>
            You have no permission to access the page, please click the <a href="main.php">link</a> to back to Main page.
        </h2>
<?php } ?>

<?php if(!$USER->authenticated && $USER->role == "user" || $USER->role == "user") { ?>
            <h2>
                You're been logged out automatically. Please <a href="main.php">re-log in</a> your accout.
            </h2>
<?php } ?>

<?php if(!$USER->authenticated) { ?>
            <h2>
                You're not allowed to visit this page. Try to <a href="main.php">re-log in</a> or <a href="register.php">register a new account.</a>
            </h2>
<?php } ?>
        </div>
    </body>
    <script>
        var list = document.getElementsByClassName('cc');
        for(var i = 0; i < list.length; i++)
        {
            document.getElementById(i).addEventListener('click', function(){
                User.processDoAdmin();
            }, true);
        }

    </script>
</html>
