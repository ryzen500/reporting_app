<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}
// Cek apakah ID ada di POST request
if (!isset($_POST['pasienadmisi_id']) || empty($_POST['pasienadmisi_id'])) {
    sendResponse(400, ['error' => 'ID tidak boleh kosong']);
    exit;
}
$pasienadmisi_id = intval($_POST['pasienadmisi_id']); // Konversi ID ke integer untuk keamanan
$pendaftaran_id = intval($_POST['pendaftaran_id']); // Konversi ID ke integer untuk keamanan
$keteranganrespontime_id = intval($_POST['keteranganrespontime_id']); // Konversi ID ke integer untuk keamanan

$keterangan = ($_POST['keterangan']);
$jenis = ($_POST['jenis']); 
date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
$tanggalSekarang = date("Y-m-d H:i:s"); // Format: 2025-03-12 15:30:45
try {
    if(empty($keteranganrespontime_id)){
        // Persiapkan query dengan parameter yang benar
        $query = "INSERT INTO keteranganrespontime_t 
        (pendaftaran_id, pasienadmisi_id, ruangan_id, jenis, keterangan, create_time, create_loginpemakai_id, create_ruangan) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
        // Gunakan `pg_prepare` untuk mencegah SQL 
    
        pg_prepare($conn, "insert_respon_time", $query);
    
        // Eksekusi query dengan nilai yang benar
        $result = pg_execute($conn, "insert_respon_time", [
            $pendaftaran_id,
            $pasienadmisi_id,
            $_SESSION['ruangan_id'],
            $jenis,
            $keterangan,
            $tanggalSekarang,
            $_SESSION['loginpemakai_id'],
            $_SESSION['ruangan_id']
        ]);    
    }else{
        // Gunakan `pg_prepare` untuk mencegah SQL Injection
        pg_prepare($conn, "update_respon_time", "UPDATE keteranganrespontime_t SET keterangan = $1,update_time=$2,update_loginpemakai_id=$3 WHERE keteranganrespontime_id = $4");

        // Eksekusi query
        $result = pg_execute($conn, "update_respon_time", [$keterangan, $tanggalSekarang, $_SESSION['loginpemakai_id'], $keteranganrespontime_id]);
    }
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
