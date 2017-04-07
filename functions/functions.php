<?php
/**
 * Created by PhpStorm.
 * User: joseph
 * Date: 27/03/17
 * Time: 16:20
 */

function clean($string){
    return htmlentities($string);
}

function redirect($location){
    return header("location: {$location}");
}

function set_message($message){
    if(!empty($message)){
        $_SESSION['message'] = $message;
    }else{
        $message = "";
    }
}

function display_message(){
    if(isset($_SESSION['message'])){
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

function token_generator(){
    $token =  $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    return $token;
}


function validation_errors($error_message){
    $error_message = <<<DELIMITER
   <div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong>Warning!</strong> $error_message
  </div>
DELIMITER;

    echo $error_message;
}

function email_exists($email){
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = query($sql);
    if(row_count($result) == 1) {
        return true;
    }else{
        return false;
    }
}
function username_exists($username){
    $sql = "SELECT id FROM users WHERE username = '$username'";
    $result = query($sql);
    if(row_count($result) == 1) {
        return true;
    }else{
        return false;
    }
}

/**
 * @param $email
 * @param $subject
 * @param $msg
 * @param $headers
 */
function send_email($email, $subject, $msg, $headers){
    return mail($email, $subject, $msg, $headers);
}






/*********************** Validation functions ***************************/

function validate_user_registration(){
    $errors = [];
    $min = 3;
    $max = 20;

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $first_name = clean($_POST['first_name']);
        $last_name = clean($_POST['last_name']);
        $username = clean($_POST['username']);
        $email = clean($_POST['email']);
        $password = clean($_POST['password']);
        $confirm_password = clean($_POST['confirm_password']);

        if (strlen($first_name) < $min) {
            $errors[] = "Your first name cannot be less than {$min} characters";

        }
        if (strlen($first_name) > $max) {
            $errors[] = "Your first name cannot be more than {$max} characters";
        }
        if (strlen($last_name) < $min) {
            $errors[] = "Your last name cannot be less than {$min} characters";

        }
        if (strlen($last_name) > $max) {
            $errors[] = "Your last name cannot be more than {$max} characters";
        }
        if (strlen($username) < $min) {
            $errors[] = "Your username cannot be less than {$min} characters";

        }
        if (strlen($username) > $max) {
            $errors[] = "Your username cannot be more than {$max} characters";
        }

        if (username_exists($username)) {
            $errors[] = "Your Username already exists";
        }
        if (email_exists($email)) {
            $errors[] = "Your email already exists";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Your passwords fields do not match";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo validation_errors($error);

            }
        } else {
            if (register_user($first_name, $last_name, $username, $email, $password)) {
                set_message("<p class='bg-success text-center'>Please check your email for an activation link</p>");
                redirect("index.php");

            }else{
                set_message("<p class='bg-danger text-center'>Sorry we could not register the user.</p>");
                redirect("index.php");
            }
        }
    }

}
/* **************************************Register user ***********************/
function register_user($first_name, $last_name, $username, $email, $password){
    $first_name = escape($first_name);
    $last_name  = escape($last_name);
    $username   = escape($username);
    $email      = escape($email);
    $password   = escape($password);

    if(email_exists($email)){
        return false;
    }elseif (username_exists($username)){
        return false;
    }else{
        $password = md5($password);
        $validation = md5($username + microtime());
        $sql = "INSERT INTO  users (first_name, last_name,username,email,password,validation_code, active)";
        $sql .= " VALUES ('$first_name','$last_name','$username','$email','$password','$validation','0') ";
        //var_dump($sql);
        $result = query($sql);

        http://www.lemp.dev/activate.php?email=dobrien@gmail.com&code=2743173ce848f660d4dc03491c9833b8        $subject = "Activate account";
        $msg = "Please click the link to activate your Account http://www.lamp.dev/login/activate.php?email=$email&code=$validation";

        $header = "From: noreply@lamp.dev";
        $subject = "Put something in here";
        send_email($email, $subject,$msg,$header);
        return true;
    }
}

/* **************************************Activate user ***********************/

function activate_user(){

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        if(isset($_GET['email'])){
            $email = clean($_GET['email']);
            $validation_code = clean($_GET['code']);
            $sql = "SELECT id FROM users WHERE email = '".escape($_GET['email'])."' AND validation_code ='".escape($_GET['code']). " ' ";
            $result = query($sql);
            confirm($result);
            //$resultSet = fetch_array($result);
            //$id = $resultSet['id'];
            if(row_count($result)== 1){
                $sql = "UPDATE users SET active = '1', validation_code = '0'  WHERE email ='".escape($email)."'";
                //var_dump($sql);
                $result2 = query($sql);
                confirm($result2);
                //echo "<p class='bg-success'>Your account has been activated.</p>";
                set_message("<p class='bg-success'>Your account has been activated");
                redirect("login.php");
            }else{
                set_message("<p class='bg-danger'>Your account could not be activated");
                redirect("login.php");
            }
        }

    }

}


function validate_user_login(){
    $errors = [];
    $min = 3;
    $max = 20;

    if($_SERVER['REQUEST_METHOD'] == "POST") {

        $email = clean($_POST['email']);
        $password = clean($_POST['password']);
        $remember = isset($_POST['remember']);

        if(empty($email)){
            $error[] = "Email field cannot be empty";

        }
        if(empty($password)){
            $error[] = "Password field cannot be empty";

        }
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo validation_errors($error);

            }
        } else {
            if(login_user($email,$password,$remember)){
                redirect("admin.php");
            }else{
                echo validation_errors("Your credentials are not correct");
            }

        }

    }

}

/**
 * @param $email
 * @param $password
 * @return bool
 */
function login_user($email, $password, $remember){

    $sql = "SELECT id, password FROM users WHERE email = '" . escape($email) . "' AND active = 1";
    //var_dump($sql);
    //die();
    $result = query($sql);
    if(row_count($result) == 1){
        $row = fetch_array($result);
        $db_password = $row['password'];
        if(md5($password === $db_password)){
            if($remember == "on"){
                setcookie('email',$email, time() + 86400);
            }
            $_SESSION['email'] = $email;
            return true;
        } else {
            return false;
        }

    }
    else {
        return false;
    }

}

/**
 * @return bool
 */
function logged_in(){

    if(isset($_SESSION['email']) || isset($_COOKIE['email'])){
        return true;
    } else {return false; }

}


//recover password
function recover_password()
{
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) {
            $email = clean($_POST['email']);
            if (email_exists($email)) {
                $validation_code = md5($email + microtime());
                setcookie('temp_access_code', $validation_code, time() + 60*30);

                $sql = "UPDATE users SET validation_code = '" . escape($validation_code) . "' WHERE email ='" . escape($email) . "'";
                $result = query($sql);
                confirm($result);
                $subject = "Please reset your password";
                $message = " Here is your password reset code {$validation_code}
            
            Click here to reset your password http://www.lemp.dev/code.php?email=$email&code=$validation_code
            
            ";
                $headers = "From: noreply@lemp.dev";

                if (!send_email($email, $subject, $message, $headers)) {

                    echo validation_errors("Email cannot be sent");
                }
                set_message("<p class='bg-success text-center'>Please check your email for a password reset. </p>");
                redirect("index.php");

            }
        } else {
            redirect("index.php");
        }
    }
}


/******************* Code Validation function ************************/
function validate_code(){

    if(isset($_COOKIE['temp_access_code'])) {
        if (!isset($_GET['email']) && !isset($_GET['code'])) {
            redirect("index.php");
        } else if (empty(($_GET['email'])) || empty($_GET['code'])) {
            redirect("index.php");
        } else {
            if (isset($_POST['code'])) {
                $email = clean($_GET['email']);

                $validation_code = clean($_POST['code']);
                $sql = "SELECT id FROM users WHERE validation_code = '" . escape($validation_code) . "'AND email ='" . escape($email) . "'";
                $result = query($sql);
                confirm($result);
                if (row_count($result) == 1) {
                    redirect("reset.php");
                } else {
                    echo validation_errors("Sorry wrong validation code");

                }
                echo "getting post from form";

            }
        }
    }
    else {
        set_message("<p class='bg-success text-center'>Sorry your validation cookie has expired. </p>");
        redirect("recover.php");

    }

}
