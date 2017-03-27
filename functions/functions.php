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
    $token = $_SESSION['token'] = md5(uniqueid(mt_rand(), true));
    return $token;
}



