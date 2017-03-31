<?php
/**
 * Created by PhpStorm.
 * User: joseph
 * Date: 27/03/17
 * Time: 16:22
 */

$con = mysqli_connect('localhost', 'root','password','login_db');


function row_count($result){
    return  mysqli_num_rows($result);

}


function escape($string){
    global $con;
    return mysqli_real_escape_string($con, $string);
}


/**
 * @param $query
 * @return bool|mysqli_result
 */
function query($query){
    global $con;

    return mysqli_query($con,$query);
}


function confirm($result){
    global $con;
    if(!$result){
        die("QUERY FAILED " . mysqli_error($con));
    }
}

/**
 * @param $result
 * @return array|null
 */
function fetch_array($result){
    global $con;

    return mysqli_fetch_array($result);
}


