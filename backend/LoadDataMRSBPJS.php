<?php
require_once 'config.php';
session_start(); // Pastikan session dimulai

class LoadDataMRSBPJS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    
    public function getData($draw, $limit, $offset, $searchValue, $no_rekam_medik = null, $nama_pasien = null, $periode = null, $ruanganSelect = [], $dateRangePicker = null, $session_instalasi_id, $session_ruangan_id,$sudahMRS, $no_pendaftaran) {
        $baseQuery = " FROM laporanmrsri_v WHERE 1=1 ";
        $params = [];
        $paramIndex = 1;

        // Instalasi yang diizinkan
        $allowed_instalasi = [2, 8, 3, 73];

        if ($ruanganSelect == "" && !in_array($session_instalasi_id, $allowed_instalasi)) {
            // Jika instalasi_id tidak sesuai, paksa filter ruangan_id = 7
            $baseQuery .= " AND ruangan_id = $" . $paramIndex;
            $params[] = 7;
            $paramIndex++;
        } else {
            // var_dump($ruanganSelect);die;
            // Jika instalasi_id sesuai, filter berdasarkan ruangan dari session
            if (!empty($ruanganSelect) && sizeof($ruanganSelect) == 1 && $ruanganSelect[0] !== "") {
                $baseQuery .= " AND ruangan_id = $" . $paramIndex;
                $params[] = $ruanganSelect[0];
                $paramIndex++;
            }
        }
        // var_dump($params);die;

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
        // Filter berdasarkan nama_pasien
        if (!empty($no_pendaftaran)) {
            $baseQuery .= " AND no_pendaftaran ILIKE $" . $paramIndex;
            $params[] = "%".$no_pendaftaran."%";
            $paramIndex++;
        }

        
                // Pilih kolom tanggal berdasarkan periode yang dipilih
            $column = "tgl_pendaftaran"; // Default
            if (!empty($periode)) {
                switch ($periode) {
                    case "Pendaftaran":
                        $column = "tgl_pendaftaran";
                        break;
                    case "Admisi":
                        $column = "tgladmisi";
                        break;
                    case "Terima":
                        $column = "tgl_timbangterima";
                        break;
                    case "Advis":
                        $column = "tgl_advismrs";
                        break;
                }
            }

            // Jika dateRangePicker diisi, gunakan sebagai filter utama
            if (!empty($dateRangePicker)) {
                $dates = explode(" to ", $dateRangePicker);
                if (count($dates) === 2) {
                    $startDate = trim($dates[0]);
                    $endDate = trim($dates[1]);

                    $baseQuery .= "AND $column is not null  AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                    $params[] = $startDate. " 00:00:00";
                    $params[] = $endDate. " 23:59:59";
                    $paramIndex += 2;
                }
                else{
                    $startDate = trim($dates[0]);
                    $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                    $params[] = $startDate. " 00:00:00";
                    $params[] = $startDate. " 23:59:59";
                    $paramIndex += 2;
                }
            } 
            // Jika dateRangePicker kosong tetapi periode diisi, gunakan filter default awal dan akhir bulan
            else{
                date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
                $tanggalSekarang = date("Y-m-d"); // Format: 2025-03-12 
                $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                $params[] = $tanggalSekarang. " 00:00:00";
                $params[] = $tanggalSekarang. " 23:59:59";
                $paramIndex += 2;
            }
            // var_dump($ruanganSelect);die;
        // Filter berdasarkan ruangan dari input user (jika instalasi_id valid)
    // Filter berdasarkan ruangan dari input user (jika instalasi_id valid)
    if (!empty($ruanganSelect)  && sizeof($ruanganSelect) > 1) {
        $placeholders = [];
        
        foreach ($ruanganSelect as $ruangan) {
            $placeholders[] = "$" . $paramIndex; // Buat placeholder untuk parameter
            $params[] = $ruangan; // Tanpa wildcard karena pakai IN
            $paramIndex++;
        }
    
        
        // Menggunakan IN dengan placeholder yang sesuai
        $baseQuery .= " AND ruangan_id IN (" . implode(", ", $placeholders) . ")";
    }
    
        if($sudahMRS === "true") { 
            $baseQuery .= " AND pasienadmisi_id is not null";
        }

        // var_dump($sudahMRS );die;
        // Hitung total data sebelum filtering
        $countTotalQuery = "SELECT COUNT(*)" . $baseQuery;
        $totalRecords = pg_fetch_result(pg_query_params($this->conn, $countTotalQuery, $params), 0, 0);
        // Hitung total data setelah filtering
        $countFilteredQuery = "SELECT COUNT(*)" . $baseQuery;
        $totalFiltered = pg_fetch_result(pg_query_params($this->conn, $countFilteredQuery, $params), 0, 0);

        // Ambil data sesuai pagination
        $query = "SELECT *" . $baseQuery . " ORDER BY pendaftaran_id DESC LIMIT $" . $paramIndex . " OFFSET $" . ($paramIndex + 1);     
        $params[] = $limit;
        // var_dump($params,$query);die;

        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("PostgreSQL Error: " . pg_last_error($this->conn));
            echo json_encode(["error" => pg_last_error($this->conn)]);
            exit;
        }
        

        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            // Ambil data utama
            $rowData = $row;
            $totalWaktu = '-';
            $color ='black';
            $keterangan='Jam Advis MRS atau Jam Timbang Terima Kosong';
            if(!empty($row['tgl_timbangterima']) && !empty($row['tgl_advismrs']) ){
                $tgl_timbangterima = new DateTime($row['tgl_timbangterima']); // Konversi ke DateTime
                $tgl_advismrs = new DateTime($row['tgl_advismrs']); // Konversi ke DateTime
                $diff = $tgl_timbangterima->diff($tgl_advismrs);
                $menit = ($diff->h * 60)+$diff->i;
                $totalWaktu = "{$diff->days} hari, {$diff->h} jam, {$diff->i} menit";
                if($tgl_timbangterima > $tgl_advismrs){
                    if($menit>90){
                        $color='red';
                        $keterangan='Lebih dari 90 menit';
    
                    }else{
                        $color='green';
                        $keterangan='Kurang dari 90 menit';
                    }
                }else{
                    $color='red';
                    $keterangan='Lebih dari 90 menit (Jam Advis MRS lebih besar dari jam timbang terima)';
                }
            }
            // Ambil data tambahan berdasarkan `pendaftaran_id`
            $baseQuery1 = "SELECT r.ruangan_nama, t.* 
            FROM keteranganrespontime_t t  
            JOIN ruangan_m r ON t.ruangan_id = r.ruangan_id 
            WHERE t.pendaftaran_id = $1 
            AND (is_deleted = $2 OR is_deleted IS NULL)
            AND jenis = $3
            ORDER by t.keteranganrespontime_id desc";
            $resultDetails = pg_query_params($this->conn, $baseQuery1, [$row['pendaftaran_id'], 'false', 'Respon Time MRS']);

            $detailData = [];
            while ($detailRow = pg_fetch_assoc($resultDetails)) {
                $detailData[] = $detailRow;
            }
                
            // Ambil lookup value dari `lookup_m`
            $lookupQuery = "SELECT lookup_value FROM lookup_m WHERE lookup_type = 'insertketerangan'";
            $resultLookup = pg_query($this->conn, $lookupQuery);

            $lookupValues = [];
            while ($lookupRow = pg_fetch_assoc($resultLookup)) {
                $lookupValues[] = $lookupRow['lookup_value'];
            }

            // Variabel baru untuk menyimpan hasil lookup
            $lookupInsertKeterangan = $lookupValues;

            // Tambahkan data tambahan ke dalam `rowData`
            $rowData['lookupInsertKeterangan'] = $lookupInsertKeterangan;
            $rowData['loopKeterangan'] = $detailData;
            $rowData['color'] = $color;
            $rowData['totalWaktu'] = $totalWaktu;
            $rowData['keteranganTotal'] = $keterangan;

            $data[] = $rowData;
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
$no_pendaftaran = isset($_GET['no_pendaftaran']) ? $_GET['no_pendaftaran'] : "";
$sudahMRS = !empty($_GET['sudahMRS']) ? $_GET['sudahMRS'] : false;
// Ambil instalasi_id dan ruangan_id dari session
$session_instalasi_id = !empty( $_SESSION['instalasi_id']) ?  $_SESSION['instalasi_id'] : null;
$session_ruangan_id = !empty( $ruanganSelect[0]) ?  $ruanganSelect  : $_SESSION['ruangan_id'];


// Validasi apakah session tersedia
if ($session_instalasi_id === null || $session_ruangan_id === null) {
    die(json_encode([
        "status" => "error",
        "message" => "Session instalasi_id atau ruangan_id tidak ditemukan."
    ]));
}

$loadData = new LoadDataMRSBPJS($conn);
$data = $loadData->getData($draw, $limit, $offset, $searchValue, $no_rekam_medik, $nama_pasien, $periode, $ruanganSelect, $dateRangePicker, $session_instalasi_id, $session_ruangan_id,$sudahMRS, $no_pendaftaran);

header('Content-Type: application/json');
echo json_encode($data);
?>
