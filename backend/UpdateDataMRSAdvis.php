<?php
require_once 'config.php';

class UpdateDataMRSAdvis {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // New function to update tgl_timbangterima and pegawai_timbangterima
    public function updateData($pendaftaran_id, $tgl_advismrs, $pegawai_advismrs) {
        // Sanitize inputs to prevent SQL injection
        $pendaftaran_id = pg_escape_string($this->conn, $pendaftaran_id);
        $tgl_advismrs = pg_escape_string($this->conn, $tgl_advismrs);
        $pegawai_advismrs = pg_escape_string($this->conn, $pegawai_advismrs);

        // Prepare SQL query
        $updateQuery = "UPDATE pendaftaran_t
                        SET tgl_advismrs = $1, pegawai_advismrs = $2
                        WHERE pendaftaran_id = $3";

        // Execute the query
        $result = pg_query_params($this->conn, $updateQuery, [$tgl_advismrs, $pegawai_advismrs, $pendaftaran_id]);

        if ($result) {
            return ["status" => "success", "message" => "Data updated successfully"];
        } else {
            return ["status" => "error", "message" => pg_last_error($this->conn)];
        }
    }
}

// For update functionality
if (($_POST['pendaftaran_id']) && ($_POST['tgl_advismrs'])) {
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $tgl_advismrs = $_POST['tgl_advismrs'];
    // var_dump($tgl_timbangterima);die;
    $pegawai_advismrs = $_POST['pegawai_advismrs'];
    
    $updateData = new UpdateDataMRSAdvis($conn);
    $response = $updateData->updateData($pendaftaran_id, $tgl_advismrs, $pegawai_advismrs);
    echo json_encode($response);
    exit;
}
?>
