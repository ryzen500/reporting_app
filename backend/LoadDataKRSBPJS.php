<?php
require_once 'config.php';

class LoadDataKRSBPJS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData($draw, $limit, $offset, $searchValue, $periode, $dateRangePicker, $nama_pasien, $no_rekam_medik, $pasienBpjs, $sudahKRS, $ruanganSelect) {
        if($pasienBpjs==1){          
            $baseQuery = " FROM laporankrsri_v WHERE carabayar_id = 2";
        }else{
            $baseQuery = " FROM laporankrsri_v WHERE carabayar_id <> 2";
        }
        $params = [];
        $paramIndex = 1;

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
        // Tambahkan filter pencarian
        // if (!empty($ruanganSelect) && sizeof($ruanganSelect) >= 1 && $ruanganSelect[0] !== "") {
        //     $baseQuery .= " AND ruangan_id = $" . $paramIndex;
        //     $params[] = $ruanganSelect[0];
        //     $paramIndex++;
        // }
        if (!empty($ruanganSelect)  && sizeof($ruanganSelect) >= 1) {
            $placeholders = [];
            
            foreach ($ruanganSelect as $ruangan) {
                $placeholders[] = "$" . $paramIndex; // Buat placeholder untuk parameter
                $params[] = $ruangan; // Tanpa wildcard karena pakai IN
                $paramIndex++;
            }
            // Menggunakan IN dengan placeholder yang sesuai
            $baseQuery .= " AND ruangan_id IN (" . implode(", ", $placeholders) . ")";
        }

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
        // Pilih kolom tanggal berdasarkan periode yang dipilih
        $column = "tglpulang"; // Default
        if (!empty($periode)) {
            switch ($periode) {
                case "Krs":
                    $column = "tglpulang";
                    break;
                case "Pembayaran":
                    $column = "tglpembayaran";
                    break;
                case "Advis":
                    $column = "tgl_adviskrs";
                    break;
            }
        }
        // Jika dateRangePicker diisi, gunakan sebagai filter utama
        if (!empty($dateRangePicker)) {
            $dates = explode(" to ", $dateRangePicker);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                $params[] = $startDate;
                $params[] = $endDate;
                $paramIndex += 2;
            }else{
                $startDate = trim($dates[0]);
                $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
                $params[] = $startDate;
                $params[] = $startDate;
                $paramIndex += 2;
            }
        }else{
            date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
            $tanggalSekarang = date("Y-m-d"); // Format: 2025-03-12 
            $baseQuery .= " AND $column BETWEEN $" . $paramIndex . " AND $" . ($paramIndex + 1);
            $params[] = $tanggalSekarang;
            $params[] = $tanggalSekarang;
            $paramIndex += 2;
        }
        if($sudahKRS==1){          
            $baseQuery .= " AND tglpulang is not null" ;
            // $params[] = "%".$sudahKRS."%";
            // $paramIndex++;
        }
        // Hitung total data sebelum filtering
        $countTotalQuery = "SELECT COUNT(*)" . $baseQuery;
        $totalRecords = pg_fetch_result(pg_query_params($this->conn, $countTotalQuery, $params), 0, 0);

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
            // Ambil data utama
            $rowData = $row;
            $totalWaktu = '-';
            $color ='black';
            $keterangan='Jam Advis KRS atau Jam Pasien Pulang Kosong';
            if(!empty($row['tglpulang']) && !empty($row['tgl_adviskrs']) ){
                $tglpulang = new DateTime($row['tglpulang']); // Konversi ke DateTime
                $tgl_adviskrs = new DateTime($row['tgl_adviskrs']); // Konversi ke DateTime
                $diff = $tglpulang->diff($tgl_adviskrs);
                $menit = ($diff->h * 60)+$diff->i;
                $totalWaktu = "{$diff->days} hari, {$diff->h} jam, {$diff->i} menit";
                if($diff->days>0){
                    $color='red';
                    $keterangan='Lebih dari 90 menit';

                }else if($menit>90){
                    $color='red';
                    $keterangan='Lebih dari 90 menit';

                }else{
                    $color='green';
                    $keterangan='Kurang dari 90 menit';

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
            $resultDetails = pg_query_params($this->conn, $baseQuery1, [$row['pendaftaran_id'], 'false', 'Respon Time KRS']);
            $detailData = [];
            while ($detailRow = pg_fetch_assoc($resultDetails)) {
                $detailData[] = $detailRow;
            }
    
            // Tambahkan data tambahan ke dalam `rowData`
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

// Ambil parameter dari frontend
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$searchValue = isset($_GET['searchValue']) ? $_GET['searchValue'] : "";
$periode = isset($_GET['periode']) ? $_GET['periode'] : "";
$dateRangePicker = isset($_GET['dateRangePicker']) ? $_GET['dateRangePicker'] : "";
$nama_pasien = isset($_GET['nama_pasien']) ? $_GET['nama_pasien'] : "";
$no_rekam_medik = isset($_GET['no_rekam_medik']) ? $_GET['no_rekam_medik'] : "";
$pasienBpjs = isset($_GET['pasienBpjs']) ? $_GET['pasienBpjs'] : "";
$sudahKRS = isset($_GET['sudahKRS']) ? $_GET['sudahKRS'] : "";
$ruanganSelect = isset($_GET['ruanganSelect']) ? $_GET['ruanganSelect'] : "";

$loadData = new LoadDataKRSBPJS($conn);
$data = $loadData->getData($draw, $limit, $offset, $searchValue, $periode, $dateRangePicker, $nama_pasien, $no_rekam_medik, $pasienBpjs, $sudahKRS, $ruanganSelect );

header('Content-Type: application/json');
echo json_encode($data);

?>
