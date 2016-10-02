<?php

    class User
    {
        // logout automatically after 300 second (5 mins)
        const time_out = 300;

        // Guest user
        const GUEST_USER = "GUEST";

        // all possible values for a hexadecimal number
        var $hex = "0123456789abcdef";

        // the regular expression for SHA1 hash matching
        const sha1regexp = "/[0123456789abcdef]{40,40}/";

        // for non-authenticated users
        var $username = USER::GUEST_USER;

        var $role = "";

        var $authenticated = false;

        var $database = false;

        var $reg = false;

        var $reset = false;

        var $update = false;

        var $error = '';

        function __construct()
        {
            // session mangment
            @session_start();

            if (empty($_SESSION["username"]) || empty($_SESSION["token"]))
            {
                 $this->resetSession();
            }

            // connect to the database
            $this->database = new SQLite3("users.db");

            // proccess a page quest
            $this->processRequest();
        }

        function processRequest()
        {
            if(isset($_POST["op"]))
            {
                $operation = $_POST["op"];

                if($operation == "login")
                {
                    $this->authenticated = $this->login();
                }
                else if($operation == "register")
                {
                    $this->reg = $this->register();
                }
                else if($operation == "logout")
                {
                    $this->logout();
                }
                else if($operation == "resetpassword")
                {
                    $this->reset = $this->reset_password();
                }
                else if($operation == "admin_update")
                {

                    $this->update = $this->update_info_by_admin();
                }
            }

            // check whether users are still active when they're surfing pages.
            if(!$this->authenticated)
            {
                $username = $_SESSION["username"];

                if($username != USER::GUEST_USER)
                {
                    $this->authenticated = $this->verify_users_active($username);

                    if($this->authenticated)
                    {
                        $this->set_user_active($username);
                    }
                }
            }

            // used for global variables
            $this->username = $_SESSION["username"];
            $this->role = $this->get_user_role($this->username);

            $this->database->close();
            $this->database = null;
        }

        function login()
        {
            $username = $_POST["username"];
            $sha1 = $_POST['sha1'];

            // use regular expression to valide a passowrd hashed by sha1
            if(preg_match(User::sha1regexp, $sha1) == 0)
            {
                $this->error = "The password did not pass validation.";
                return false;
            }

            // get user's password and token from database
            $db_password = $this->token_hash_password($username, $sha1, $this->get_user_token($username));

            if($db_password == $sha1)
            {
                $this->error = "password hashing is not implemented.";
                return false;
            }

            // use prepare statement to avoid SQL inject
            if($stmt = $this->database->prepare("SELECT * FROM users WHERE username = ? LIMIT 1"))
            {
                $stmt->bindValue(1, $username, SQLITE3_TEXT);
                $ret = $stmt->execute();
                $row = $ret->fetchArray(SQLITE3_ASSOC);

                if($row["password"] == $db_password)
                {
                    // set user active and serssion variable
                    $this->set_user_active($row["username"]);
                    $this->setSession($row["username"], $this->update_user_token($row["username"], $sha1));
                    return true;
                }
            }

            $this->error = "Username or Password is invalid";

            return false;
        }

        function register()
        {
            $username = $_POST['username'];
            $sha1 = $_POST['sha1'];
            $token = "";

            // use regular expression to valide a passowrd hashed by sha1
            if(preg_match(User::sha1regexp, $sha1) == 0)
            {
                $this->error = "The password did not pass validation.";
                return false;
            }

            $db_password = $this->token_hash_password($username, $sha1, $token);

            if($db_password == $sha1)
            {
                $this->error = "password hashing is not implemented.";
                return false;
            }

            // TODO: check whether the username exists.
            if($stmt = $this->database->prepare("SELECT username FROM users"))
            {
                $ret = $stmt->execute();

                while($row = $ret->fetchArray(SQLITE3_ASSOC))
                {
                    if($username == $row["username"])
                    {
                        $this->error = "The username already exists";
                        return false;
                    }
                }
            }

            // insert the username to the database
            if($stmt = $this->database->prepare("INSERT INTO users (username, password, token, role, active, last) VALUES (?, ?, ?, ?, ?, ?)"))
            {
                $time = time();

                $stmt->bindValue(1, $username, SQLITE3_TEXT);
                $stmt->bindValue(2, $db_password, SQLITE3_TEXT);
                $stmt->bindValue(3, '', SQLITE3_TEXT);
                $stmt->bindValue(4, "user", SQLITE3_TEXT);
                $stmt->bindValue(5, "true", SQLITE3_TEXT);
                $stmt->bindValue(6, $time, SQLITE3_TEXT);
                $stmt->execute();

                // find the registed username and update his token
                $stmt = $this->database->prepare("SELECT * FROM users WHERE username = '$username' LIMIT 1");
                $stmt->bindValue(1, $username, SQLITE3_TEXT);
                $ret = $stmt->execute();
                if((count($ret)) == 1)
                {
                    $row = $ret->fetchArray(SQLITE3_ASSOC);
                    $this->update_user_token($row["username"], $sha1);
                    return true;
                }
            }

            return false;
        }

        function logout()
        {
            $username = $_SESSION["username"];

            if($stmt = $this->database->prepare("UPDATE users SET active = ? WHERE username = ?"))
            {
                $stmt->bindValue(1, 'false', SQLITE3_TEXT);
                $stmt->bindValue(2, $username, SQLITE3_TEXT);
                $stmt->execute();
                $this->resetSession();
                //echo "Log '$username' out , set active as false<br />";
                return true;
            }

            return false;
        }

        function reset_password()
        {
            $username = $_SESSION["username"];
            $sha1 = $_POST["sha1"];

            // compared his own token with session-token in database
            $token = $this->get_user_token($username);
            if($token != $_SESSION["token"])
            {
                $this->error = "token mismatch for '$username' ";
                return false;
            }

            // use regular expression to valide a passowrd hashed by sha1
            if($sha1 != "" && preg_match(User::sha1regexp, $sha1) != 0)
            {
                $this->update_user_token($username, $sha1);
                return true;
            }

            $this->error = "The password did not pass validation.";
            return false;
        }

        function verify_users_active($username)
        {
            $last = 0;
            $active = false;

            if($stmt = $this->database->prepare("SELECT last, active FROM users WHERE username = '$username' LIMIT 1"))
            {
                $stmt->bindValue(1, $username, SQLITE3_TEXT);
                $ret = $stmt->execute();
                $row = $ret->fetchArray(SQLITE3_ASSOC);

                if((count($ret) == 1))
                {
                    $last = intval($row["last"]);
                    $active = $row["active"];
                }
                else
                {
                    $this->error = "DB unkown error";
                    return false;
                }

                // check a user whether the logining is over than 5 mins.
                if($active == true)
                {
                    $diff = time() - $last;

                    if($diff >= User::time_out)
                    {
                        $this->logout($username);
                        $this->error = "User is active but time out ";
                        return false;
                    }

                    // logged in, but do the tokens match?
                    $token = $this->get_user_token($username);
                    if($token != $_SESSION["token"])
                    {
                        $this->logout($username);
                        $this->error = "token mismatch for '$username'";
                        return false;
                    }

                    // active, using the correct token -> authenticated
                    return true;
                }
            }

            $this->error = "This user is not active ";
            $this->resetSession();
            return false;
        }

        function update_user_token($username, $sha1)
        {
            // generate a random vaule of token for a user
            $token = $this->random_hex_string(32);

            if($stmt = $this->database->prepare("UPDATE users SET token = ? WHERE username = ?"))
            {
                $stmt->bindValue(1, $token, SQLITE3_TEXT);
                $stmt->bindValue(2, $username, SQLITE3_TEXT);
                $stmt->execute();
            }

            // update the user's password based on the token
            $new_password = $this->token_hash_password($username, $sha1, $token);

            if($stmt = $this->database->prepare("UPDATE users SET password = ? WHERE username = ?"))
            {
                $stmt->bindValue(1, $new_password, SQLITE3_TEXT);
                $stmt->bindValue(2, $username, SQLITE3_TEXT);
                $stmt->execute();
            }

            //echo "Update user token and password <br />";
            return $token;
        }

        function update_user_role($username, $new_role)
        {

            if($stmt = $this->database->prepare("UPDATE users SET role = ? WHERE username = ?"))
            {
                $stmt->bindValue(1, $new_role, SQLITE3_TEXT);
                $stmt->bindValue(2, $username, SQLITE3_TEXT);
                $stmt->execute();
                //echo "Update user role as '$new_role' <br />";
                return true;
            }

            return false;
        }

        function set_user_active($username)
        {
            if($stmt = $this->database->prepare("UPDATE users SET active = ?, last = ? WHERE username = ?"))
            {
                // get current time
                $time = time();

                $stmt->bindValue(1, "true", SQLITE3_TEXT);
                $stmt->bindValue(2, $time, SQLITE3_TEXT);
                $stmt->bindValue(3, $username, SQLITE3_TEXT);
                $stmt->execute();
                //echo "$username has been marked currently active. <br />";
                return true;
            }
            return false;
        }

        function get_user_token($username)
        {
            if($stmt = $this->database->prepare("SELECT token FROM users WHERE username = ? LIMIT 1"))
            {
                $stmt->bindValue(1, $username, SQLITE3_TEXT);
                $ret = $stmt->execute();

                if((count($ret)) == 1)
                {
                    $row = $ret->fetchArray(SQLITE3_ASSOC);
                    return $row["token"];
                }
            }
            return false;
        }

        function get_user_role($username)
        {
            if($username != "" && $username != User::GUEST_USER)
            {
                $query = "SELECT role FROM users WHERE username = '$username' LIMIT 1";

                $ret = $this->database->query($query);

                if((count($ret)) == 1)
                {
                    $row = $ret->fetchArray(SQLITE3_ASSOC);
                    return $row["role"];
                }
            }

            return User::GUEST_USER;
        }

        function get_all_users()
        {
            $this->database = new SQLite3("users.db");

            $user_info = array();

            if($stmt = $this->database->prepare("SELECT username FROM users"))
            {
                $ret = $stmt->execute();

                while($row = $ret->fetchArray(SQLITE3_ASSOC))
                {
                    array_push($user_info, $row["username"]);
                }
            }
            $this->database->close();
            $this->database = null;

            return $user_info;
        }

        function update_info_by_admin()
        {
            //var_dump($_POST);
            $target_user = $_POST["username"];
            $sha1 = $_POST["sha1"];
            $new_role = $_POST["role"];

            // use regular expression to valide a passowrd hashed by sha1
            if($sha1 != "" && preg_match(User::sha1regexp, $sha1) == 0)
            {
                $this->error = "The password did not pass validation.";
                return false;
            }

            if($stmt = $this->database->prepare("SELECT * FROM users where username = ? LIMIT 1"))
            {
                $stmt->bindValue(1, $target_user, SQLITE3_TEXT);
                $ret = $stmt->execute();

                if(count($ret) == 1)
                {
                    // update new passowrd
                    if($sha1 != "")
                        $this->update_user_token($target_user, $sha1);

                    // update his or her role
                    if($new_role != "")
                        $this->update_user_role($target_user, $new_role);

                    return true;
                }
            }

            $this->error = " DB Unkown error ";
            return false;

        }

        function setSession($username, $token)
        {
            //echo "call setSession<br />";
            //echo "Username = '$username'<br />";
            $_SESSION["username"] = $username;
            $_SESSION["token"] = $token;
        }

        function resetSession()
        {
            //echo "call resetSession <br />";
            $_SESSION["username"] = User::GUEST_USER;
            $_SESSION["token"] = -1;
        }

        /**
         * Random hex string generator
         */
        function random_hex_string($length)
        {
            $string = "";
            $max = strlen($this->hex) - 1;

            while($length > 0)
            {
                // using mt_rand to generate random values
                $string .= $this->hex[mt_rand(0, $max)];

                $length--;
            }

            return $string;
        }

        /**
		 * The incoming password will already be a sha1 print (40 bytes) long,
		 * but for the database we want it to be hased as sha256 (using 64 bytes).
		 */
        function token_hash_password($username, $sha1, $token)
        {
            return hash("sha256", $username . $sha1 . $token);
        }
    }
?>
