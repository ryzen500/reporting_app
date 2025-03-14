<?php
require_once 'config.php';

class LoadDataKRSBPJS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData($draw, $limit, $offset, $searchValue) {
        $baseQuery = " FROM laporankrsri_v WHERE carabayar_id = 2";
        $params = [];
        $paramIndex = 1;

        // Tambahkan filter pencarian
        if (!empty($searchValue)) {
            $baseQuery .= " AND (ruangan_nama ILIKE $" . $paramIndex . 
                          " OR no_rekam_medik ILIKE $" . ($paramIndex + 1) . 
                          " OR nama_pasien ILIKE $" . ($paramIndex + 2) . ")";
            $params[] = "%".$searchValue."%";
            $params[] = "%".$searchValue."%";
            $params[] = "%".$searchValue."%";
            $paramIndex += 3;
        }

        // Hitung total data sebelum filtering
        $countTotalQuery = "SELECT COUNT(*)" . $baseQuery;
        $totalRecords = pg_fetch_result(pg_query_params($this->conn, $countTotalQuery, []), 0, 0);

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

$loadData = new LoadDataKRSBPJS($conn);
$data = $loadData->getData($draw, $limit, $offset, $searchValue);

header('Content-Type: application/json');
echo json_encode($data);

?>
