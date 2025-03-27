<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}
// Cek apakah ID ada di POST request
if (!isset($_POST['keteranganrespontime_id']) || empty($_POST['keteranganrespontime_id'])) {
    sendResponse(400, ['error' => 'ID tidak boleh kosong']);
    exit;
}

$keteranganrespontime_id = intval($_POST['keteranganrespontime_id']); // Konversi ID ke integer untuk keamanan
date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
$tanggalSekarang = date("Y-m-d H:i:s"); // Format: 2025-03-12 15:30:45
try {
    // Gunakan `pg_prepare` untuk mencegah SQL Injection
    pg_prepare($conn, "update_respon_time", "UPDATE keteranganrespontime_t SET is_deleted = $1 ,delete_loginpemakai_id = $2,delete_time= $3 WHERE keteranganrespontime_id = $4");

    // Eksekusi query
    $result = pg_execute($conn, "update_respon_time", [true, $_SESSION['loginpemakai_id'], $tanggalSekarang, $keteranganrespontime_id]);
    if ($result) {
        sendResponse(200, ['status'=>'success','success' => 'Data Berhasil Disimpan']);
    } else {
        sendResponse(500, ['status'=>'error','error' => 'Gagal simpan data']);
    }
} catch (\Throwable $th) {
    //throw $th;
    sendResponse(500, ['status'=>'error','error' => 'Gagal simpan data']);

}


// Tutup koneksi (opsional)
pg_close($conn);

?>
