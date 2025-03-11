CREATE DATABASE `form_self_assesment` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;


CREATE TABLE `login_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `login_time` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) 


CREATE TABLE `tb_self_assessment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_rekam_medik` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `usia` int DEFAULT NULL,
  `alamat` text,
  `kelurahan_desa` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `batuk_lebih_dari_2_minggu` enum('ya','tidak') DEFAULT NULL,
  `batuk_kurang_dari_2_minggu` enum('ya','tidak') DEFAULT NULL,
  `batuk_berdarah` enum('ya','tidak') DEFAULT NULL,
  `berat_badan_turun` enum('ya','tidak') DEFAULT NULL,
  `nafsu_makan_turun` enum('ya','tidak') DEFAULT NULL,
  `demam` enum('ya','tidak') DEFAULT NULL,
  `mudah_lelah` enum('ya','tidak') DEFAULT NULL,
  `berkeringat_malam_hari_tanpa_aktivitas` enum('ya','tidak') DEFAULT NULL,
  `sesak_nafas` enum('ya','tidak') DEFAULT NULL,
  `nyeri_dada` enum('ya','tidak') DEFAULT NULL,
  `ada_benjolan_di_leher_rahang_bawah_telinga_ketiak` enum('ya','tidak') DEFAULT NULL,
  `anggota_keluarga` enum('ya','tidak','tidak tahu') DEFAULT NULL,
  `pernah_tinggal_serumah_minimal_satu_malam` enum('ya','tidak','tidak tahu') DEFAULT NULL,
  `pernah_berada_di_satu_ruangan_dengan_penderita_tbc` enum('ya','tidak','tidak tahu') DEFAULT NULL,
  `punya_riwayat_diabetes` enum('ya','tidak') DEFAULT NULL,
  `kurang_gizi` enum('ya','tidak') DEFAULT NULL,
  `orang_dengan_hiv_positif` enum('ya','tidak') DEFAULT NULL,
  `merokok_perokok_pasif` enum('ya','tidak') DEFAULT NULL,
  `ibu_hamil` enum('ya','tidak') DEFAULT NULL,
  `lansia_usia_diatas_60_tahun` enum('ya','tidak') DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `noformulir` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
)


CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `create` tinyint(1) DEFAULT '0',
  `read` tinyint(1) DEFAULT '1',
  `update` tinyint(1) DEFAULT '0',
  `delete` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) 