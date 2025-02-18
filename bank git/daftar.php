<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "db_atm"; 

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputName = htmlspecialchars($_POST['username'] ?? ''); 
    $inputPin = $_POST['password'] ?? ''; 

    // Cek apakah input kosong
    if (!empty($inputName) && !empty($inputPin)) {
        // Cek apakah username sudah digunakan
        $checkUser = $conn->prepare("SELECT username FROM tb_bank WHERE username = ?");
        $checkUser->bind_param("s", $inputName);
        $checkUser->execute();
        $checkUser->store_result();

        if ($checkUser->num_rows > 0) {
            echo "Username sudah digunakan, silakan pilih username lain.";
            $checkUser->close();
            exit;
        }
        $checkUser->close();

        // Validasi PIN
        if (!preg_match('/^[0-9]{4,6}$/', $inputPin)) {
            echo "PIN harus berupa angka dengan panjang 4-6 karakter.";
            exit;
        }

        // Hash PIN sebelum menyimpan ke database
        $hashedPin = password_hash($inputPin, PASSWORD_DEFAULT);

        // Simpan data ke database
        $stmt = $conn->prepare("INSERT INTO tb_bank (username, pin) VALUES (?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ss", $inputName, $hashedPin);
            if ($stmt->execute()) {
                echo "Akun berhasil dibuat.";
                $stmt->close();
                $conn->close();
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
    <style>
        body {
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #0e3c9a;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
    <title>Daftar Akun</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h3 class="text-center">Daftar Akun</h3>
                    <form method="post" action="register.php">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PIN</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Daftar</button>
                        <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Masuk</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
