<?php
require_once("process.php");

$USER = new User();

//var_dump($_SESSION);

?>

<!DOCTYPE html>
<html>
    <head>
        <title> Main page </title>
        <link rel="stylesheet" type="text/css" href="css/style.css"  />
        <script type="text/javascript" src="js/sha1.js"></script>
        <script type="text/javascript" src="js/user.js"></script>
    </head>

    <body>
        <div id="main">
<?php if(!$USER->authenticated) { ?>
            <div id="login">
                <h2>
                    Login System
                </h2>
                <form id="log_in" action="main.php" method="POST">
                    <input type="hidden" name="op" value="login"/>
                    <input type="hidden" name="sha1" value=""/>
                    <label>UserName : </label>
                    <input id="name" name="username" type="text" value=""/>
                    <label>password : </label>
                    <input id="password" name="password" type="password" value=""/>
                    <input type="button" value=" Login " onclick="User.processLogin()" />
                    <a href="register.php"> Register ? <br /></a>
                    <span id="error"><?php echo $USER->error; ?></span>
                </form >
            </div>
<?php } ?>

<?php if($USER->authenticated) { ?>
            <h2>
                Hi <?php echo $USER->username; ?>, welcome back ! You can do :

                <?php if($USER->role == "user" || $USER->role == "admin") { ?>

                <input type="button" value="Password Management" onclick="window.location.assign('password.php')" />
                <input type="button" value="User Management" onclick="window.location.assign('admin.php')" />

                <form id="log_out" action="main.php" method="POST">
                    <input type="hidden" name="op" value="logout"/>
                    <input type="button" value=" Logout " onclick="User.processLogout()" />
                </form>
                <?php } ?>

            </h2>
<?php } ?>
        </div>
    </body>
</html>
