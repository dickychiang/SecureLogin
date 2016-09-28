<?php
require_once("login.php");


?>

<!DOCTYPE html>
<html>
    <head>
        <title> Main Form </title>
        <link rel="stylesheet" type="text/css" href="css/style.css"  />
    </head>

    <body>
        <div id="main">
            <h1>
                Login Session
            </h1>
            <div id="login">
                <h2>
                    Login Form
                </h2>
                <form action="" method="POST">
                    <label>UserName : </label>
                    <input id="name" name="username" placeholder="username" type="text" />
                    <label>password : </label>
                    <input id="password" nme="password" placeholder="******" type="password"/>
                    <input name="submit" type="submit" value=" Login " />
                    <span><?php echo $error; ?></span>
                </form>
            </div>
        </div>
    </body>
</html>
