<?php
require_once 'config.php';

class LoadRuangan {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData() {
        // Perbaikan: Gunakan pg_query() karena tidak ada parameter dalam query
        $query1 = "SELECT * FROM ruangan_m";
        $result1 = pg_query($this->conn, $query1);

        // Periksa apakah query berhasil
        if (!$result1) {
            return [
                "status" => "error",
                "message" => "Query error: " . pg_last_error($this->conn)
            ];
        }

        $data = [];
        while ($row = pg_fetch_assoc($result1)) {
            $data[] = $row;
        }

        return [
            "status" => "success",
            "options" => $data
        ];
    }
}

// Pastikan koneksi database tersedia
if (!isset($conn)) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection is not set."
    ]));
}

// Instantiate class dan panggil fungsi
$dropdown = new LoadRuangan($conn);
$data = $dropdown->getData();

// Kirim response JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
