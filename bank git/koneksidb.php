<?php
$host = "localhost"; // Sesuaikan dengan server MySQL Anda
$user = "root"; // Sesuaikan dengan username MySQL
$pass = ""; // Sesuaikan dengan password MySQL (kosongkan jika default)
$dbname = "db_atm"; // Ganti dengan nama database yang digunakan

$koneksidb = new mysqli($host, $user, $pass, $dbname);

// Periksa koneksi
if ($koneksidb->connect_error) {
    die("Koneksi gagal: " . $koneksidb->connect_error);
}
?>
