<?php
include("../connection/connect.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/error.log'); // error log path
error_reporting(E_ALL);

session_start();

mysqli_query($db,"DELETE FROM res_category WHERE c_id = '".$_GET['cat_del']."'");
header("location:add_category.php");  

?>
