<?php
require_once 'config.php'; // Koneksi database
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}

// Cek apakah keteranganrespontime_id ada di POST request
if (!isset($_POST['keteranganrespontime_id']) || empty($_POST['keteranganrespontime_id'])) {
    die("ID tidak boleh kosong");
}

$keteranganrespontime_id = intval($_POST['keteranganrespontime_id']);

// Query untuk mengambil data keterangan berdasarkan keteranganrespontime_id
$query = "SELECT * FROM keteranganrespontime_t WHERE keteranganrespontime_id = $1";
$result = pg_query_params($conn, $query, [$keteranganrespontime_id]);

// Pastikan hasil query tidak kosong
if (!$result || pg_num_rows($result) == 0) {
    die("Data tidak ditemukan");
}

$data = pg_fetch_assoc($result); // Ambil data dalam bentuk array asosiatif

?>
<label for="keterangan"><strong>Keterangan:</strong></label>
<textarea id="keteranganMRS" name="keterangan" class="form-control" rows="3"><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
<input type="hidden" id="keteranganrespontime_id" name="keteranganrespontime_id" value="<?php echo htmlspecialchars($keteranganrespontime_id); ?>">
<hr>

<?php
// Tutup koneksi database
pg_close($conn);
?>
