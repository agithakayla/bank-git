<?php
session_start();
session_unset(); // digunakan untuk menghancurkan variabel nya 
session_destroy(); // hapus sesi nya 
header('location: index.php');
?>