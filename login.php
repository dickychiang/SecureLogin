<?php
    $error = "";

    // Starting session
    session_start();

    if(empty($_POST['username']) || empty($_POST['password']))
    {
            $error = "Username or Password is invalid";
    }
    else
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
    }

?>
