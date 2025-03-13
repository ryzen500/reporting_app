<?php
require_once 'config.php';
session_start(); // Pastikan session dimulai

class LoadDataMRSBPJS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData($draw, $limit, $offset, $searchValue, $no_rekam_medik = null, $nama_pasien = null, $periode = null, $ruanganSelect = null, $dateRangePicker = null, $session_instalasi_id, $session_ruangan_id) {
        $baseQuery = " FROM laporanmrsri_v WHERE 1=1 ";
        $params = [];
        $paramIndex = 1;

        // Instalasi yang diizinkan
        $allowed_instalasi = [2, 8, 3, 73];

        if (!in_array($session_instalasi_id, $allowed_instalasi)) {
            // Jika instalasi_id tidak sesuai, paksa filter ruangan_id = 7
            $baseQuery .= " AND ruangan_id = $" . $paramIndex;
            $params[] = 7;
            $paramIndex++;
        } else {
            // Jika instalasi_id sesuai, filter berdasarkan ruangan dari session
            if (!empty($session_ruangan_id)) {
                $baseQuery .= " AND ruangan_id = $" . $paramIndex;
                $params[] = $session_ruangan_id;
                $paramIndex++;
            }
        }

        // Tambahkan filter pencarian umum
        if (!empty($searchValue)) {
            $baseQuery .= " AND (ruangan_nama ILIKE $" . $paramIndex . 
                          " OR no_rekam_medik ILIKE $" . ($paramIndex + 1) . 
                          " OR nama_pasien ILIKE $" . ($paramIndex + 2) . ")";
            $params[] = "%".$searchValue."%";
            $params[] = "%".$searchValue."%";
            $params[] = "%".$searchValue."%";
            $paramIndex += 3;
        }

        // Filter berdasarkan no_rekam_medik
        if (!empty($no_rekam_medik)) {
            $baseQuery .= " AND no_rekam_medik ILIKE $" . $paramIndex;
            $params[] = "%".$no_rekam_medik."%";
            $paramIndex++;
        }

        // Filter berdasarkan nama_pasien
        if (!empty($nama_pasien)) {
            $baseQuery .= " AND nama_pasien ILIKE $" . $paramIndex;
            $params[] = "%".$nama_pasien."%";
            $paramIndex++;
        }

        // Filter berdasarkan periode tertentu
        if (!empty($periode)) {
            switch ($periode) {
                case "Pendaftaran":
                    $column = "tglpendaftaran";
                    break;
                case "Admisi":
                    $column = "tgladmisi";
                    break;
                case "Terima":
                    $column = "tglterima";
                    break;
                case "Advis":
                    $column = "tgladvis";
                    break;
                default:
                    $column = null;
                    break;
            }

            if ($column) {
                $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                $params[] = date("Y-m-01"); // Awal bulan
                $params[] = date("Y-m-t");  // Akhir bulan
                $paramIndex += 2;
            }
        }

        // Filter berdasarkan dateRangePicker (contoh format: "2025-03-13 to 2025-03-14")
        if (!empty($dateRangePicker)) {
            $dates = explode(" to ", $dateRangePicker);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                // Gunakan tglpendaftaran sebagai default filter (bisa disesuaikan)
                $baseQuery .= " AND tglpendaftaran BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                $params[] = $startDate;
                $params[] = $endDate;
                $paramIndex += 2;
            }
        }

        // Filter berdasarkan ruangan dari input user (jika instalasi_id valid)
        if (!empty($ruanganSelect) && in_array($session_instalasi_id, $allowed_instalasi)) {
            $baseQuery .= " AND ruangan_nama ILIKE $" . $paramIndex;
            $params[] = "%".$ruanganSelect."%";
            $paramIndex++;
        }

        // Hitung total data sebelum filtering
        $countTotalQuery = "SELECT COUNT(*) FROM laporanmrsri_v";
        $totalRecords = pg_fetch_result(pg_query($this->conn, $countTotalQuery), 0, 0);

        // Hitung total data setelah filtering
        $countFilteredQuery = "SELECT COUNT(*)" . $baseQuery;
        $totalFiltered = pg_fetch_result(pg_query_params($this->conn, $countFilteredQuery, $params), 0, 0);

        // Ambil data sesuai pagination
        $query = "SELECT *" . $baseQuery . " LIMIT $" . $paramIndex . " OFFSET $" . ($paramIndex + 1);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            echo json_encode(["error" => pg_last_error($this->conn)]);
            exit;
        }

        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return [
            "draw" => $draw,
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
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

// Ambil instalasi_id dan ruangan_id dari session
$session_instalasi_id = !empty( $_SESSION['instalasi_id']) ?  $_SESSION['instalasi_id'] : null;
$session_ruangan_id = !empty( $_SESSION['ruangan_id']) ?  $_SESSION['ruangan_id'] : null;

// Validasi apakah session tersedia
if ($session_instalasi_id === null || $session_ruangan_id === null) {
    die(json_encode([
        "status" => "error",
        "message" => "Session instalasi_id atau ruangan_id tidak ditemukan."
    ]));
}

// Ambil parameter dari frontend
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$searchValue = isset($_GET['searchValue']) ? $_GET['searchValue'] : "";
$no_rekam_medik = isset($_GET['no_rekam_medik']) ? $_GET['no_rekam_medik'] : "";
$periode = isset($_GET['periode']) ? $_GET['periode'] : "";
$dateRangePicker = isset($_GET['dateRangePicker']) ? $_GET['dateRangePicker'] : "";
$nama_pasien = isset($_GET['nama_pasien']) ? $_GET['nama_pasien'] : "";
$ruanganSelect = isset($_GET['ruanganSelect']) ? $_GET['ruanganSelect'] : "";

$loadData = new LoadDataMRSBPJS($conn);
$data = $loadData->getData($draw, $limit, $offset, $searchValue, $no_rekam_medik, $nama_pasien, $periode, $ruanganSelect, $dateRangePicker, $session_instalasi_id, $session_ruangan_id);

header('Content-Type: application/json');
echo json_encode($data);
?>
