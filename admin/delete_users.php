<?php
include("../connection/connect.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/error.log'); // error log path
error_reporting(E_ALL);

session_start();

mysqli_query($db,"DELETE FROM users WHERE u_id = '".$_GET['user_del']."'");
header("location:all_users.php");  

?>
