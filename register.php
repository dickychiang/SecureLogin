<?php
/*
 * Adds new users to the system.
 * The page asks for user information,which at the minimum should consist of a username and a password.(You may ask for additional fields.)
 * Once registered, users can use the username and password combination to authenticate themselves and login to the site.
 *
 */
require_once("process.php");

$USER = new User();

//var_dump($_SESSION);

//var_dump($USER->authenticated);

?>
<!DOCTYPE html>
<html>
    <head>
        <title> Register Form </title>
        <link rel="stylesheet" type="text/css" href="css/style.css"  />
        <script type="text/javascript" src="js/sha1.js"></script>
        <script type="text/javascript" src="js/user.js"></script>
    </head>

    <body>
        <div id="main">
<?php if(!$USER->reg) { ?>
            <div id="login">
                <h2>
                    Register Form
                </h2>
                <form id="registration" action="register.php" method="POST">
                    <input type="hidden" name="op" value="register"/>
                    <input type="hidden" name="sha1" value=""/>
                    <label>UserName : </label>
                    <input id="name" name="username" type="text" value=""/>
                    <label>password : </label>
                    <input id="password" name="password" type="password" value=""/>
                    <input type="button" value=" Register " onclick="User.processRegistration()" />
                    <input type="button" value=" Back to Main page " onclick="window.location.assign('main.php')" />
                    <span id="error"><?php echo $USER->error; ?></span>
                </form >
            </div>
<?php } ?>

<?php if($USER->reg) { ?>
            <br /><br /><br />
            <h2>
                Your account has been registered successfully !
                Click the button to back main page to log in.
            </h2>
            <input type="button" value=" Back to Main page " onclick="window.location.assign('main.php')" />
<?php } ?>
        </div>
    </body>
</html>
