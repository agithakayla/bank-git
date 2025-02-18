<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "db_atm"; 

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputName = htmlspecialchars($_POST['username'] ?? ''); 
    $inputPin = $_POST['password'] ?? ''; 

    // Cek apakah input kosong
    if (!empty($inputName) && !empty($inputPin)) {
        // Cek apakah username sudah digunakan
        $checkUser = $conn->prepare("SELECT username FROM tb_transaksi WHERE username = ?");
        $checkUser->bind_param("s", $inputName);
        $checkUser->execute();
        $checkUser->store_result();

        if ($checkUser->num_rows > 0) {
            echo "Username sudah digunakan, silakan pilih username lain.";
            $checkUser->close();
            exit;
        }
        $checkUser->close();

        // Validasi PIN (harus angka & panjang 4-6 karakter)
        if (!preg_match('/^[0-9]{4,6}$/', $inputPin)) {
            echo "PIN harus berupa angka dengan panjang 4-6 karakter.";
            exit;
        }

        // Simpan PIN langsung ke database tanpa hashing
        $stmt = $conn->prepare("INSERT INTO tb_transaksi (username, pin) VALUES (?, ?)");

        if ($stmt) {
            $stmt->bind_param("ss", $inputName, $inputPin);
            if ($stmt->execute()) {
                $_SESSION['username'] = $inputName;
                header('Location: menu.php');
                exit;
            } else {
                echo "Error saat menyimpan data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Prepare statement gagal: " . $conn->error;
        }
    } else {
        echo "Username dan PIN tidak boleh kosong.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Registrasi Akun</title>
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center">Registrasi Akun</h3>
        <form method="post" action="register.php">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>PIN</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Daftar</button>
            <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Masuk</a></p>
        </form>
    </div>
</body>
</html>
