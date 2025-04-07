<?php
require_once 'backend/config.php'; // Pastikan koneksi ke database ada di file ini

session_start();
if (!isset( $_SESSION['nama_pemakai'])) {
  header("Location: login.php");
}else{
    header("Location: admin.php");
}

?>