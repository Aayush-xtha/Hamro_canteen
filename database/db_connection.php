<?php
$host = "localhost";
$dbName = "hamro_canteen";
$user = "root";
$password = "";
$conn = mysqli_connect($host, $user, $password, $dbName);
if ($conn ->connect_errno){
    echo "failed to connect to MYSQL";
    exit();
}else{
// echo "connected Successfully";  

}