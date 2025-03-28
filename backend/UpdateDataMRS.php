<?php
require_once 'config.php';

class UpdateDataMRS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // New function to update tgl_timbangterima and pegawai_timbangterima
    public function updateData($pendaftaran_id, $tgl_timbangterima, $pegawai_timbangterima) {
        // Sanitize inputs to prevent SQL injection
        $pendaftaran_id = pg_escape_string($this->conn, $pendaftaran_id);
        $tgl_timbangterima = pg_escape_string($this->conn, $tgl_timbangterima);
        $pegawai_timbangterima = pg_escape_string($this->conn, $pegawai_timbangterima);

        // Prepare SQL query
        $updateQuery = "UPDATE pasienadmisi_t
                        SET tgl_timbangterima = $1, pegawai_timbangterima = $2
                        WHERE pendaftaran_id = $3";

        // Execute the query
        $result = pg_query_params($this->conn, $updateQuery, [$tgl_timbangterima, $pegawai_timbangterima, $pendaftaran_id]);

        if ($result) {
            return ["status" => "success", "message" => "Data updated successfully"];
        } else {
            return ["status" => "error", "message" => pg_last_error($this->conn)];
        }
    }
}

// For update functionality
if (($_POST['pendaftaran_id']) && ($_POST['tgl_timbangterima'])) {
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $tgl_timbangterima = $_POST['tgl_timbangterima'];
    // var_dump($tgl_timbangterima);die;
    $pegawai_timbangterima = $_POST['pegawai_timbangterima'];
    
    $updateData = new UpdateDataMRS($conn);
    $response = $updateData->updateData($pendaftaran_id, $tgl_timbangterima, $pegawai_timbangterima);
    echo json_encode($response);
    exit;
}
?>
