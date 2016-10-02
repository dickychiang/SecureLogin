<?php

/*
 * Allows authenticated users to change their password.
 * For unauthenticated users, accessing the page should result with an offer to login with an existing user or to register a new user
 *
 */
require_once("process.php");

$USER = new User();

//var_dump($_SESSION);
?>
<!DOCTYPE html>
<html>
    <head>
        <title> Password Management </title>
        <link rel="stylesheet" type="text/css" href="css/style.css"  />
        <script type="text/javascript" src="js/sha1.js"></script>
        <script type="text/javascript" src="js/user.js"></script>
    </head>
    <body>
        <div id="main">
<?php if($USER->authenticated) { ?>
            <div id="login">
                <h2>
                    Change <?php echo $USER->username;?>'s password
                </h2>
                <form id="resetpassword" action="password.php" method="POST">
                    <input type="hidden" name="op" value="resetpassword"/>
                    <input type="hidden" name="sha1" value=""/>
                    <label>your new password : </label>
                    <input id="password" name="password" type="password" value=""/>
                    <input type="button" value=" Update " onclick="User.processResetpassword()" />
                    <input type="button" value=" Back to Main page " onclick="window.location.assign('main.php')" />
                    <span id="error"><?php echo $USER->error; ?></span>
                </form>
            </div>
<?php } ?>

<?php if(!$USER->authenticated && $USER->reset) { ?>
            <h2>
                Your password has been updated, please <a href="main.php">re-log in</a> your account with new password.
            </h2>
<?php } ?>

<?php if(!$USER->authenticated && !$USER->reset) { ?>
            <h2>
                You have been automatically logged out or are not allowed to visit this page. Try to <a href="main.php">re-log in</a> or <a href="register.php">register a new account.</a>
            </h2>
<?php } ?>
        </div>
    </body>
</html>
