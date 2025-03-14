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
$keteranganrespontime_id = intval($_POST['keteranganrespontime_id']); // Konversi ke integer

// Query untuk mengambil data dari database
$baseQuery1 = "SELECT t.* 
               FROM keteranganrespontime_t t  
               WHERE t.keteranganrespontime_id = $1";

// Eksekusi query dengan parameter
$result = pg_query_params($conn, $baseQuery1, [$keteranganrespontime_id]);
if (!$result) {
    die("Query gagal: " . pg_last_error($conn)); // Menampilkan error dari PostgreSQL
}
$row = pg_fetch_assoc($result);

?>
<label for="keterangan"><strong>Keterangan:</strong></label>
<textarea id="keterangan" name="keterangan" class="form-control" rows="3" ><?php echo htmlspecialchars($row['keterangan']); ?></textarea>
<input type="text" id="pasienadmisi_id" name="pasienadmisi_id" hidden value="<?php echo $row['pasienadmisi_id'] ?>">
<input type="text" id="pendaftaran_id" name="pendaftaran_id" hidden value="<?php echo $row['pendaftaran_id'] ?>">
<input type="text" id="keteranganrespontime_id" name="keteranganrespontime_id" hidden value="<?php echo $row['keteranganrespontime_id'] ?>">
<hr>
<?php
// Tutup koneksi (opsional)
pg_close($conn);

?>
