
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('memory_limit', '256M'); // Or any higher value you need

require '../vendor/autoload.php';
require_once 'config.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LoadDataFormLaporanExcelMRS {
    private $conn;
    private $limit;
    private $offset;
    private $periode;
    private $dateRangePicker;
    private $nama_pasien;
    private $no_rekam_medik;
    private $pasienBpjs;
    private $sudahKRS;
    private $ruanganSelect;

    public function __construct($conn, $limit = 10, $offset = 0, $filters = []) {
        $this->conn = $conn;
        $this->limit = intval($limit);
        $this->offset = intval($offset);
        $this->periode = $filters['periode'] ?? '';
        $this->dateRangePicker = $filters['dateRangePicker'] ?? '';
        $this->nama_pasien = $filters['nama_pasien'] ?? '';
        $this->no_rekam_medik = $filters['no_rekam_medik'] ?? '';
        $this->pasienBpjs = $filters['pasienBpjs'] ?? '';
        $this->sudahKRS = $filters['sudahKRS'] ?? '';
        $this->ruanganSelect = (!empty($filters['ruanganSelect'])) ?  explode(",", $filters['ruanganSelect']):'';
    }


    public function fetchAll() {
        // Initialize the base SQL query
      
            $baseQuery = " FROM laporanmrsri_v WHERE WHERE 1=1 ";

        $sql = "SELECT *" . $baseQuery ;

        // Apply filters
        $this->applyFilters($sql);

        // Add pagination
        // $sql .= " AND tb_self_assessment.is_deleted = false LIMIT $this->limit OFFSET $this->offset";

        // Execute the query
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return ["status" => 500, "data" => ["error" => $this->conn->error]];
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);

        // GET total count for pagination
        $totalCount = $this->GETTotalCount();

        return [
            "status" => $result->num_rows > 0 ? 200 : 404,
            "data" => $data,
            "pagination" => [
                "limit" => $this->limit,
                "offset" => $this->offset,
                "total" => $totalCount
            ]
        ];
    }

    private function applyFilters(&$sql) {
        if (!empty($this->ruanganSelect)  && sizeof($this->ruanganSelect) >= 1) {
            $placeholders = [];
            
            foreach ($this->ruanganSelect as $ruangan) {
                $placeholders[] = $ruangan; // Buat placeholder untuk parameter
                // $params[] = $ruangan; // Tanpa wildcard karena pakai IN
                // $paramIndex++;
            }

            // Menggunakan IN dengan placeholder yang sesuai
            $sql .= " AND ruangan_id IN (" . implode(", ", $placeholders) . ")";

        }

        if (!empty($this->no_rekam_medik)) {
            $sql .= " AND no_rekam_medik ILIKE $" . $this->no_rekam_medik;
        }
        // Filter berdasarkan nama_pasien
        if (!empty($this->nama_pasien)) {
            $sql .= " AND nama_pasien ILIKE $" . $this->nama_pasien;
        }
        // Pilih kolom tanggal berdasarkan periode yang dipilih
        $column = "tglpulang"; // Default
        if (!empty($this->periode)) {
            switch ($this->periode) {
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
        if (!empty($this->dateRangePicker)) {
            $dates = explode(" to ", $this->dateRangePicker);
            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);

                $sql .= " AND $column BETWEEN '" . $startDate . "' AND '" . ($endDate)."'";
            }else{
                $startDate = trim($dates[0]);
                $startDate = $startDate. " 00:00:00";
                $endDate = $startDate. " 23:59:59";
                $sql .= " AND $column BETWEEN '" . $startDate . "' AND '" . ($endDate)."'";

            }
        }else{
            date_default_timezone_set('Asia/Jakarta'); // Pastikan timezone sesuai
            $tanggalSekarang = date("Y-m-d"); // Format: 2025-03-12 
            $startDate = $tanggalSekarang. " 00:00:00";
            $endDate = $tanggalSekarang. " 23:59:59";
            $sql .= " AND $column BETWEEN '" . $startDate . "' AND '" . ($endDate)."'";
        }
        
    }

    private function GETTotalCount() {
        // Prepare count query
        $countSql = "SELECT COUNT(*) as total FROM laporanmrsri_v";
        $this->applyFilters($countSql); // Apply the same filters to count query

        $countResult = $this->conn->query($countSql);
        if ($countResult) {
            return $countResult->fetch_assoc()['total'];
        }
        return 0;
    }


    public function generateExcel() {
        // Fetch all data for export
        $response = $this->fetchAllForExport();
        
        if ($response["status"] !== 200) {
            echo "Error fetching data: " . $response["data"]["error"];
            return;
        }

        $data = $response["data"];
        // Set headers for Excel file download
        // header("Content-Type: application/vnd.ms-excel");
        // header("Content-Disposition: attachment; filename=\"data_laporan.xls\"");
        // header("Pragma: no-cache");
        // header("Expires: 0");

        // // Output column headers
        // echo "No\tRuangan\tNo Rekam Medik/Nama \tAdvis KRS\tSK Ke Farmasi\tSK Ke Farmasi Selesai\tEntry Resep\tJam Pembayaran\tJam Pasien Pulang\tTotal Waktu\tKeterangan\n";
        // $no =1;
        // $row_temp=[];
        // // Output data rows
        // foreach ($data as $row) {
        //     // Convert specific fields to strings for Excel
        //     $row_temp['no'] = $no;
        //     $row_temp['ruangan'] = $row['ruangan_nama'];
        //     $row_temp['no_rekam_medik'] = $row['no_rekam_medik'].' / '.$row['nama_pasien'];
        //     if( ($row['tgl_adviskrs'] === ''||$row['tgl_adviskrs'] ===null) &&($row['pegawai_adviskrs'] === ''||$row['pegawai_adviskrs'] ===null) ){
        //         $row_temp['advis_krs'] = $row['tgl_adviskrs'].' / '.$row['pegawai_adviskrs'];
        //     }else{
        //         $row_temp['advis_krs'] = '-';
        //     }
        //     if( ($row['tgl_skfarmasi'] === ''||$row['tgl_skfarmasi'] ===null) &&($row['pegawai_skfarmasi'] === ''||$row['pegawai_skfarmasi'] ===null) ){
        //         $row_temp['sk_farmasi'] = $row['tgl_skfarmasi'].' / '.$row['pegawai_skfarmasi'];
        //     }else{
        //         $row_temp['sk_farmasi'] = '-';
        //     }
        //     if($row['tgl_verifikasifarmasi'] ==='' || $row['tgl_verifikasifarmasi']===null){
        //         $row_temp['tgl_verifikasifarmasi'] = $row['tgl_verifikasifarmasi'] ;
        //     }else{
        //         $row_temp['tgl_verifikasifarmasi'] = '-' ;
        //     }
        //     if($row['tglpembayaran'] ==='' || $row['tglpembayaran']===null){
        //         $row_temp['tglpembayaran'] = $row['tglpembayaran'] ;
        //     }else{
        //         $row_temp['tglpembayaran'] = '-' ;
        //     }
        //     if($row['tglpulang'] ==='' || $row['tglpulang']===null){
        //         $row_temp['tglpulang'] = $row['tglpulang'] ;
        //     }else{
        //         $row_temp['tglpulang'] = '-' ;
        //     }
        //     $row_temp['totalWaktu'] = $row['totalWaktu'].' <span style="color:'.$row['color'].'">'.$row['keteranganTotal'] ."</span>";

        //     // echo"<pre>";var_dump(sizeof($row['loopKeterangan']));die;
        //     // $row['no_rekam_medik'] = (string)$row['no_rekam_medik'];
        //     // $row['nik'] = '=' . (string)$row['nik'] . '';
        //     // $row['no_telp'] = (string)$row['no_telp'];
        //     // $row['lansia_usia_diatas_60_tahun'] = (string)$row['lansia_usia_diatas_60_tahun'];

        //     // // Replace empty values with "-"
        //     // $row = array_map(function($value) {
        //     //     return $value !== null && $value !== '' ? $value : '-';
        //     // }, $row);
        //     $no++;
        //     echo implode("\t", $row_temp) . "\n";
        // }

        // Buat objek spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Header kolom
        $headers = ["No", "Ruangan", "No Rekam Medik / Nama", "Advis KRS", "SK Ke Farmasi", "SK Ke Farmasi Selesai", "Entry Resep", "Jam Pembayaran", "Jam Pasien Pulang", "Total Waktu", "Keterangan"];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Tambahkan data
        $rowNum = 2;
        $no = 1;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Header kolom
        $headers = ["No", "Ruangan", "No Rekam Medik / Nama", "Advis MRS", "Terbit SPRI", "Selesai Pendaftaran ", "Jam Timbang Terima", "Total Waktu", "Keterangan"];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Tambahkan data
        $rowNum = 2;
        $no = 1;
        foreach ($data as $row) {
            $namaPasien = str_replace("<br>", "\n", "{$row['no_rekam_medik']} / {$row['nama_pasien']}");
       
            // echo "<pre>";
            // var_dump($row);die;
            $tanggalAdvis  = (!empty($row['tgl_advismrs'])) ?  str_replace("<br>", "\n", "{$row['tgl_advismrs']} / {$row['pegawai_advismrs']}") : '-';
            $terbitSPRI = (!empty($row['tgl_suratperintahranap'])) ? str_replace("<br>", "\n", "{$row['tgl_suratperintahranap']}") : '-';
            $tgladmisi = (!empty($row['tgladmisi'])) ? str_replace("<br>", "\n", "{$row['tgladmisi']}") : '-';
            $tgl_timbangterima  = (!empty($row['tgl_timbangterima'])) ?  str_replace("<br>", "\n", "{$row['tgl_timbangterima']} / {$row['pegawai_timbangterima']}") : '-';


            // $ket='';
            // if(sizeof($row['loopKeterangan'])>0){
            //     foreach($row['loopKeterangan'] as $ket_row){
            //         $ket.= $ket_row['ruangan_nama']. ' : '.$ket_row['keterangan'].", \n";
            //     }
            // }
            $sheet->setCellValue("A$rowNum", $no);
            $sheet->setCellValue("B$rowNum", $row['ruangan_nama']);
            $sheet->setCellValue("C$rowNum", $namaPasien);
            // $sheet->setCellValue("D$rowNum", "{$row['tgl_adviskrs']} / {$row['pegawai_adviskrs']}");
            // $sheet->setCellValue("E$rowNum", "{$row['tgl_skfarmasi']} / {$row['pegawai_skfarmasi']}");
            $sheet->setCellValue("D$rowNum", $tanggalAdvis);
            $sheet->setCellValue("E$rowNum", $terbitSPRI);
            $sheet->setCellValue("F$rowNum", $tgladmisi);
            $sheet->setCellValue("G$rowNum", $tgl_timbangterima);
            $sheet->setCellValue("H$rowNum", $row['totalWaktu']);
            // $sheet->setCellValue("I$rowNum", $row['totalWaktu']);
            // $sheet->setCellValue("I$rowNum", "{$row['totalWaktu']} / {$row['keteranganTotal']}");

            // Gunakan RichText untuk kolom I agar keteranganTotal bisa diwarnai
            $richText = new RichText();
            $richText->createText("{$row['totalWaktu']} / \n"); // Tambahkan newline setelah totalWaktu

            $textKeterangan = $richText->createTextRun($row['keteranganTotal']);
            
            if ($row['color'] == 'red') {
                $textKeterangan->getFont()->setColor(new Color(Color::COLOR_RED));
            } elseif ($row['color'] == 'green') {
                $textKeterangan->getFont()->setColor(new Color(Color::COLOR_GREEN));
            }

            $sheet->getCell("J$rowNum")->setValue($richText);
            // $sheet->getStyle("I$rowNum")->getAlignment()->setWrapText(true); // Agar teks bisa turun ke baris baru
            // $sheet->setCellValue("K$rowNum", $ket);
            // Gunakan RichText untuk format yang lebih baik pada keterangan
            $richTextKet = new RichText();

            if (sizeof($row['loopKeterangan'])>0) {
                foreach ($row['loopKeterangan'] as $ket_row) {
                    // Buat teks bold untuk ruangan_nama
                    $textBold = $richTextKet->createTextRun($ket_row['ruangan_nama'] . ' : ');
                    $textBold->getFont()->setBold(true); // Jadikan bold

                    // Tambahkan keterangan setelahnya
                    $textNormal = $richTextKet->createTextRun($ket_row['keterangan'] . "\n"); // Tambahkan newline
                }
            }else{
                $textBold = $richTextKet->createTextRun('-');
            }

            // Masukkan richText ke kolom K (Keterangan)
            $sheet->getCell("K$rowNum")->setValue($richTextKet);
            $sheet->getStyle("K$rowNum")->getAlignment()->setWrapText(true); // Supaya teks turun ke baris baru


            $no++;
            $rowNum++;
        }
        // ✅ Atur Lebar Kolom Otomatis
        foreach (range('A', 'K') as $col) { 
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Simpan file Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = "data-export.xlsx";
        // header("Content-Type: application/vnd.ms-excel");
        // header("Content-Disposition: attachment; filename=\"data_laporan.xls\"");
        // header("Pragma: no-cache");
        // header("Expires: 0");
        ob_end_clean(); // Hapus output sebelumnya
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"data-export.xlsx\"");
        header("Cache-Control: max-age=0");
        $writer->save("php://output");
        exit();


    }

    public function fetchAllForExport() {
       
            $baseQuery = " FROM laporanmrsri_v WHERE 1=1 ";
        $params = [];
        $paramIndex = 1;
        if (!empty($this->ruanganSelect)  && sizeof($this->ruanganSelect) >= 1) {
            $placeholders = [];
            
            foreach ($this->ruanganSelect as $ruangan) {
                $placeholders[] = "$" . $paramIndex; // Buat placeholder untuk parameter
                $params[] = $ruangan; // Tanpa wildcard karena pakai IN
                $paramIndex++;
            }
            // Menggunakan IN dengan placeholder yang sesuai
            $baseQuery .= " AND ruangan_id IN (" . implode(", ", $placeholders) . ")";
        }

        if (!empty($this->no_rekam_medik)) {
            $baseQuery .= " AND no_rekam_medik ILIKE $" . $paramIndex;
            $params[] = "%".$this->no_rekam_medik."%";
            $paramIndex++;
        }
        // Filter berdasarkan nama_pasien
        if (!empty($this->nama_pasien)) {
            $baseQuery .= " AND nama_pasien ILIKE $" . $paramIndex;
            $params[] = "%".$this->nama_pasien."%";
            $paramIndex++;
        }
        // Pilih kolom tanggal berdasarkan periode yang dipilih
        $column = "tglpulang"; // Default
        if (!empty($this->periode)) {
            switch ($this->periode) {
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
        if (!empty($this->dateRangePicker)) {
            $dates = explode(" to ", $this->dateRangePicker);
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
                $params[] = $startDate. " 00:00:00";
                $params[] = $startDate. " 23:59:59";
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


        $query = "SELECT *" . $baseQuery;
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
            $keterangan='Jam Advis MRS atau Jam Timbang Terima Kosong';
            if(!empty($row['tgl_timbangterima']) && !empty($row['tgl_advismrs']) ){
                $tgl_timbangterima = new DateTime($row['tgl_timbangterima']); // Konversi ke DateTime
                $tgl_advismrs = new DateTime($row['tgl_advismrs']); // Konversi ke DateTime
                $diff = $tgl_timbangterima->diff($tgl_advismrs);
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
            "status" => 200,
            "data" => $data
        ];
    }

    // Method for generating PDF file
    // public function generatePDF($data) {

    //     $pdf = new \Dompdf\Dompdf();
    //     $html = "<h1>Laporan Data Self Assessment</h1>";
    //     $html .= "<table border='1'><tr><th>No Formulir</th><th>Poli Tujuan</th><th>No Rekam Medik</th><th>Nama</th><th>NIK</th><th>Jenis Kelamin</th><th>Terduga</th></tr>";

    //     foreach ($data as $row) {
    //         $html .= "<tr><td>{$row['noformulir']}</td><td>{$row['poli_tujuan']}</td><td>{$row['no_rekam_medik']}</td><td>{$row['nama']}</td><td>{$row['nik']}</td><td>{$row['jenis_kelamin']}</td><td>{$row['terduga']}</td></tr>";
    //     }

    //     $html .= "</table>";
    //     $pdf->loadHtml($html);
    //     $pdf->setPaper('A4', 'landscape');
    //     $pdf->render();
    //     $pdf->stream("data_laporan.pdf", array("Attachment" => true));
    // }
}

// Fetch limit, filters, and action from query parameters
$limit = $_GET['limit'] ?? 10;
$offset = $_GET['offset'] ?? 0;
$action = $_GET['action'] ?? '';
$filters = [
    'periode' => isset($_GET['periode']) ? $_GET['periode'] : "",
    'dateRangePicker' => isset($_GET['dateRangePicker']) ? $_GET['dateRangePicker'] : "",
    'nama_pasien' => isset($_GET['nama_pasien']) ? $_GET['nama_pasien'] : "",
    'no_rekam_medik' => isset($_GET['no_rekam_medik']) ? $_GET['no_rekam_medik'] : "",
    'pasienBpjs' => isset($_GET['pasienBpjs']) ? $_GET['pasienBpjs'] : "",
    'sudahKRS' => isset($_GET['sudahKRS']) ? $_GET['sudahKRS'] : "",
    'ruanganSelect' => isset($_GET['ruanganSelect']) ? $_GET['ruanganSelect'] : ""
];


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $dataLoader = new LoadDataFormLaporanExcelMRS($conn, $limit, $offset, $filters);

    if ($action === 'export_excel') {

        $response = $dataLoader->fetchAllForExport();

        // var_dump($response);die;
        if ($response['status'] === 200) {
            $dataLoader->generateExcel($response['data']);
        } else {
            sendResponse($response['status'], $response['data']);
        }
    } else {
        $response = $dataLoader->fetchAll();
        // Send response in DataTables format
        if ($response['status'] === 200) {
            sendResponse(200, [
                "draw" => intval($_GET['draw'] ?? 0), // DataTables draw counter
                "recordsTotal" => $response['pagination']['total'], // Total records in the database
                "recordsFiltered" => $response['pagination']['total'], // Total records after filtering
                "data" => $response['data'] // Data to display
            ]);
        } else {
            sendResponse($response['status'], $response['data']);
        }
    }
}
?>
