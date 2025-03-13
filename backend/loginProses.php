<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
include 'config.php';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'UNKNOWN';
}

function disableNTBin($value) {
    $hex = bin2hex($value);
    $hex = str_replace("00", "88", $hex);
    return hex2bin($hex);
}

function cekPassword3($value, $katakunciPemakai, $seckey, $namaPemakai) {
    $pass = hash_hmac("sha256", $value . "&" . $namaPemakai, $seckey, true);

    try {
        $is_verify = password_verify($pass, base64_decode($katakunciPemakai));
        if (!$is_verify) {
            $pass = disableNTBin($pass);
            return password_verify($pass, base64_decode($katakunciPemakai));
        }
        return $is_verify;
    } catch (\Exception $e) {
        $pass = disableNTBin($pass);
        return password_verify($pass, base64_decode($katakunciPemakai));
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $ruangan_id = isset($_POST['ruangan_id']) ? trim($_POST['ruangan_id']) : '';

    if (empty($username) || empty($password) || empty($ruangan_id)) {
        $error = "Username dan Password harus diisi";
        header("Location: ../login.php?error=" . urlencode($error));
        exit();
    }

    $query = "SELECT  pegawai_m.nama_pegawai,loginpemakai_k.* FROM loginpemakai_k left join pegawai_m on pegawai_m.pegawai_id = loginpemakai_k.pegawai_id  WHERE nama_pemakai = $1";
    $result = pg_query_params($conn, $query, [$username]);

    $queryRuangan = "SELECT  ruangan_id,instalasi_id from ruangan_m WHERE ruangan_id= $1";
    $result2 = pg_query_params($conn, $queryRuangan, [$ruangan_id]);

    if ($result) {
        $login = pg_fetch_assoc($result);
        $ruanganPemakai = pg_fetch_assoc($result2);

        if ($login) {
            $seckey = '5be7138d5324812699b0f54ed4a9243f252175f4';
            $katakunciPemakai = $login["katakunci_pemakai"];

            if (cekPassword3($password, $katakunciPemakai, $seckey, $username)) {
                $_SESSION['nama_pemakai'] = $login['nama_pemakai'];
                $_SESSION['nama_pegawai'] = $login['nama_pegawai'];
                $_SESSION['instalasi_id'] = $ruanganPemakai['instalasi_id'];
                $_SESSION['ruangan_id'] = $ruanganPemakai['ruangan_id'];


                header("Location: ../admin.php");
                exit();
            } else {
                $error = "Username atau Password salah";
            }
        } else {
            $error = "Username atau Password salah";
        }
    } else {
        $error = "Terjadi kesalahan saat memproses login.";
    }

    header("Location: ../login.php?error=" . urlencode($error));
    exit();
}
?>
