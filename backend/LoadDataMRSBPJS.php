<?php
require_once 'config.php';

class LoadDataMRSBPJS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData($draw, $limit, $offset, $searchValue) {
        $baseQuery = " FROM laporanmrsri_v ";
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

// Ambil parameter dari frontend
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$searchValue = isset($_GET['searchValue']) ? $_GET['searchValue'] : "";

$loadData = new LoadDataMRSBPJS($conn);
$data = $loadData->getData($draw, $limit, $offset, $searchValue);

header('Content-Type: application/json');
echo json_encode($data);

?>
