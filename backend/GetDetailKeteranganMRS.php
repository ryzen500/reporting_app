<?php
require_once 'config.php'; // Pastikan koneksi ke database ada di file ini
session_start();

// Pastikan koneksi PostgreSQL aktif
if (!$conn) {
    die("Koneksi ke database gagal");
}
// Cek apakah ID ada di POST request
if (!isset($_POST['pendaftaran_id']) || empty($_POST['pendaftaran_id'])) {
    sendResponse(400, ['error' => 'ID tidak boleh kosong']);
    exit;
}
?>
<label for="keterangan"><strong>Keterangan:</strong></label>
<textarea id="keterangan" name="keterangan" class="form-control" rows="3" ></textarea>
<input type="text" id="pendaftaran_id" name="pendaftaran_id" hidden value="<?php echo $_POST['pendaftaran_id'] ?>">
<hr>
<?php
// Tutup koneksi (opsional)
pg_close($conn);

?>
