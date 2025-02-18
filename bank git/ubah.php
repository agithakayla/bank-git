<?php
session_start();
include "koneksidb.php"; // Pastikan koneksi ke database benar

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$user_id = $_SESSION['username']; // Ambil username dari session

// Debug: Pastikan user_id benar
error_log("Debug: User ID dari session = " . $user_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_pin = $_POST['old_pin'];
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];

    // Debug: Tampilkan PIN yang dimasukkan user
    error_log("Debug: PIN lama dari input = " . $old_pin);

    if ($new_pin !== $confirm_pin) {
        die("<script>alert('PIN baru dan konfirmasi tidak cocok!'); window.location='ubah.php';</script>");
    }

    // 🔍 Ambil PIN lama dari tb_transaksi berdasarkan username
    $query = "SELECT Pin FROM tb_transaksi WHERE username = ?";
    $stmt = $koneksidb->prepare($query);

    if (!$stmt) {
        die("<script>alert('Query Error: " . $koneksidb->error . "'); window.location='ubah.php';</script>");
    }

    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("<script>alert('Data tidak ditemukan! Pastikan username benar.'); window.location='ubah.php';</script>");
    }

    $stmt->bind_result($stored_pin);
    $stmt->fetch();

    // Debug: Tampilkan PIN yang ada di database
    error_log("Debug: PIN di database = " . $stored_pin);

    // 🔍 Periksa apakah PIN lama cocok (karena tidak hash, pakai ===)
    if ($stored_pin === $old_pin) { 
        error_log("Debug: PIN cocok! Melanjutkan update...");
        
        // 🔄 Update PIN baru di database (langsung tanpa hashing)
        $update_query = "UPDATE tb_transaksi SET Pin = ? WHERE username = ?";
        $stmt = $koneksidb->prepare($update_query);

        if (!$stmt) {
            die("<script>alert('Query Update Error: " . $koneksidb->error . "'); window.location='ubah.php';</script>");
        }

        $stmt->bind_param("ss", $new_pin, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<script>alert('PIN berhasil diubah!'); window.location='menu.php';</script>";
        } else {
            echo "<script>alert('Gagal mengubah PIN. Coba lagi!'); window.location='ubah.php';</script>";
        }
    } else {
        error_log("Debug: PIN tidak cocok!");
        die("<script>alert('PIN lama salah! Pastikan input benar.'); window.location='ubah.php';</script>");
    }

    // Tutup koneksi
    $stmt->close();
    $koneksidb->close();
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah PIN</title>
    <style>
        body {
            background-color: #0e3c9a;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #0e3c9a;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            text-align: left;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #0c1153;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #a6a0dd;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Ubah PIN Anda</h2>
        <form action="ubah.php" method="POST">
            <label for="old_pin">PIN Lama</label>
            <input type="password" id="old_pin" name="old_pin" required>

            <label for="new_pin">PIN Baru</label>
            <input type="password" id="new_pin" name="new_pin" required>

            <label for="confirm_pin">Konfirmasi PIN Baru</label>
            <input type="password" id="confirm_pin" name="confirm_pin" required>

            <input type="submit" value="Ubah PIN">
        </form>
    </div>
</body>
</html>
