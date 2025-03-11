<?php

$environment = 'development';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($environment == "production") {

    $host = "192.168.214.225";
    $port = "5121";
    $dbname = "db_rswb_running_new";
    $user = "developer";
    $password = "s6SpprwyLVqh7kFg";
    
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
    
}else{

    $host = "192.168.214.225";
    $port = "5121";
    $dbname = "db_rswb_running_new";
    $user = "developer";
    $password = "s6SpprwyLVqh7kFg";
    
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
    
 
}


if (!$conn) {
    // die("Koneksi ke PostgreSQL gagal: " . pg_last_error());
} else {
    // echo "Koneksi berhasil!";
}

function sendResponse($status, $data = null) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

?>
