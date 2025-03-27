<?php
require_once 'config.php';

class DropdownLoginSIMRS {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getData($username) {
        // First query: Fetch loginpemakai_k data based on the username
        $query1 = "SELECT * FROM loginpemakai_k WHERE nama_pemakai = $1";
        $result1 = pg_query_params($this->conn, $query1, [$username]);

        if (!$result1) {
            echo json_encode(["error" => pg_last_error($this->conn)]);
            exit;
        }

        $loginpemakai = pg_fetch_assoc($result1);
        
        if (!$loginpemakai) {
            // If no matching record is found
            echo json_encode(["error" => "No user found with the given username"]);
            exit;
        }

        // Get loginpemakai_id
        $loginpemakai_id = $loginpemakai['loginpemakai_id'];

        // Second query: Fetch data from ruanganpemakai_k based on loginpemakai_id
        $query2 = "SELECT ruangan_m.ruangan_nama,ruanganpemakai_k.* FROM ruanganpemakai_k left join ruangan_m on ruangan_m.ruangan_id = ruanganpemakai_k.ruangan_id WHERE loginpemakai_id = $1";
        $result2 = pg_query_params($this->conn, $query2, [$loginpemakai_id]);

        if (!$result2) {
            echo json_encode(["error" => pg_last_error($this->conn)]);
            exit;
        }

        $data = [];
        while ($row = pg_fetch_assoc($result2)) {
            $data[] = $row;
        }

        // Return the fetched data
        return [
            "status" => "success",
            "options" => $data
        ];
    }
}

// Instantiate the class and get the username from the request
if (isset($_GET['username'])) {
    $username = $_GET['username']; // Get username parameter from request

    $dropdown = new DropdownLoginSIMRS($conn); // Assuming you have a $conn (database connection)
    $data = $dropdown->getData($username);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    // If no username is provided
    echo json_encode(["status" => "error", "message" => "Username parameter is missing"]);
}
?>
