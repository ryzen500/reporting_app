<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}

// Periksa apakah semua data yang dibutuhkan dikirim
$requiredFields = ['pendaftaran_id',  'keterangan'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        sendResponse(400, ['error' => "Field $field tidak boleh kosong"]);
        exit;
    }
}

// Ambil data dari POST request
$pendaftaran_id = intval($_POST['pendaftaran_id']);
$ruangan_id = $_SESSION['ruangan_id'];
$jenis = trim("Respon Time MRS");
$keterangan = trim($_POST['keterangan']);
$create_loginpemakai_id = $_SESSION['pegawai_id'] ?? 0; // ID pengguna dari session
$create_ruangan = $_SESSION['ruangan_id'] ?? null; // ID ruangan dari session (opsional)
date_default_timezone_set('Asia/Jakarta');
$create_time = date("Y-m-d H:i:s");

// Cek apakah pasienadmisi_id ada di tabel pasienadmisi_t
pg_prepare($conn, "check_pasienadmisi", "SELECT pasienadmisi_id FROM pasienadmisi_t WHERE pendaftaran_id = $1");
$result = pg_execute($conn, "check_pasienadmisi", [$pendaftaran_id]);
$row = pg_fetch_assoc($result);
$pasienadmisi_id = $row ? intval($row['pasienadmisi_id']) : null; // Jika tidak ditemukan, set null

// Gunakan `pg_prepare` untuk mencegah SQL Injection pada INSERT
pg_prepare($conn, "insert_keteranganrespontime", 
    "INSERT INTO keteranganrespontime_t (pendaftaran_id, pasienadmisi_id, ruangan_id, jenis, keterangan, create_time, create_loginpemakai_id, create_ruangan) 
    VALUES ($1, $2, $3, $4, $5, $6, $7, $8)"
);

// Eksekusi query INSERT
$result = pg_execute($conn, "insert_keteranganrespontime", [
    $pendaftaran_id, $pasienadmisi_id, $ruangan_id, $jenis, $keterangan, $create_time, $create_loginpemakai_id, $create_ruangan
]);

// Berikan response
if ($result) {
    sendResponse(200, ['success' => 'Data berhasil disimpan']);
} else {
    sendResponse(500, ['error' => 'Gagal menyimpan data']);
}

// Tutup koneksi (opsional)
pg_close($conn);

?>
