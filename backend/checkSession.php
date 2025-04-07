<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Cek apakah session 'user_id' ada
if (!isset( $_SESSION['nama_pemakai'])) {
    http_response_code(401); // Set status code 401
    echo json_encode(['status' => 'error', 'message' => 'Session tidak aktif, harap login kembali']);
}else{
    echo json_encode(['status' => 'success', 'message' => 'Session aktif']);
}

?>
