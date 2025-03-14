<?php
require_once 'config.php'; // Pastikan koneksi ke database
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}

// Periksa apakah semua data yang dibutuhkan dikirim
$requiredFields = ['keteranganrespontime_id'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        sendResponse(400, ['error' => "Field $field tidak boleh kosong"]);
        exit;
    }
}

// Ambil data dari POST request
$keteranganrespontime_id = intval($_POST['keteranganrespontime_id']);
$keterangan =true;
$update_loginpemakai_id = $_SESSION['loginpemakai_id'] ?? 0; // ID pengguna yang mengupdate
date_default_timezone_set('Asia/Jakarta');
$update_time = date("Y-m-d H:i:s");

// Gunakan `pg_prepare` untuk mencegah SQL Injection pada UPDATE
pg_prepare($conn, "update_keteranganrespontimes", 
    "UPDATE keteranganrespontime_t 
     SET is_deleted = TRUE, delete_time = $1, delete_loginpemakai_id = $2 
     WHERE keteranganrespontime_id = $3"
);

// Eksekusi query UPDATE
$result = pg_execute($conn, "update_keteranganrespontimes", [
     $update_time, $update_loginpemakai_id, $keteranganrespontime_id
]);

// Berikan response
if ($result) {
    sendResponse(200, ['success' => 'Data berhasil diperbarui']);
} else {
    sendResponse(500, ['error' => 'Gagal memperbarui data']);
}

// Tutup koneksi (opsional)
pg_close($conn);
?>
