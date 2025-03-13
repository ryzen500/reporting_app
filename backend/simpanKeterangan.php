<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}
// Cek apakah ID ada di POST request
if (!isset($_POST['id']) || empty($_POST['id'])) {
    sendResponse(400, ['error' => 'ID tidak boleh kosong']);
    exit;
}
$id = intval($_POST['id']); // Konversi ID ke integer untuk keamanan
date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
$tanggalSekarang = date("Y-m-d H:i:s"); // Format: 2025-03-12 15:30:45

// Gunakan `pg_prepare` untuk mencegah SQL Injection
pg_prepare($conn, "update_tgl_adviskrs", "UPDATE pasienadmisi_t SET tgl_skfarmasi = $1,pegawai_skfarmasi=$2 WHERE pasienadmisi_id = $3");

// Eksekusi query
$result = pg_execute($conn, "update_tgl_adviskrs", [$tanggalSekarang, $_SESSION['nama_pegawai'], $id]);

if ($result) {
    sendResponse(200, ['success' => 'Data Berhasil Diperbarui']);
} else {
    sendResponse(500, ['error' => 'Gagal memperbarui data']);
}

// Tutup koneksi (opsional)
pg_close($conn);

?>
