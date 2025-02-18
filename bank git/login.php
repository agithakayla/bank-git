<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_atm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputName = trim($_POST['username'] ?? '');
    $inputPin = trim($_POST['password'] ?? '');

    if (!empty($inputName) && !empty($inputPin)) {
        $stmt = $conn->prepare("SELECT username, pin, login_attempts, is_blocked FROM tb_transaksi WHERE username = ?");
        if (!$stmt) {
            die("Error SQL: " . $conn->error);
        }

        $stmt->bind_param("s", $inputName);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($dbUsername, $dbPin, $loginAttempts, $isBlocked);
            $stmt->fetch();

            if ($isBlocked) {
                // Jika akun sudah diblokir, hapus akun
                $deleteStmt = $conn->prepare("DELETE FROM tb_transaksi WHERE username = ?");
                $deleteStmt->bind_param("s", $inputName);
                $deleteStmt->execute();
                $deleteStmt->close();

                echo "Akun Anda telah diblokir dan dihapus dari sistem.";
            } else {
                if (password_verify($inputPin, $dbPin) || $inputPin === $dbPin) {
                    $updateStmt = $conn->prepare("UPDATE tb_transaksi SET login_attempts = 0 WHERE username = ?");
                    $updateStmt->bind_param("s", $inputName);
                    $updateStmt->execute();
                    $updateStmt->close();

                    session_regenerate_id(true);
                    $_SESSION['username'] = $dbUsername;
                    header('Location: menu.php');
                    exit;
                } else {
                    $loginAttempts++;
                    if ($loginAttempts >= 3) {
                        $updateStmt = $conn->prepare("UPDATE tb_transaksi SET login_attempts = ?, is_blocked = 1 WHERE username = ?");
                        $updateStmt->bind_param("is", $loginAttempts, $inputName);
                        $updateStmt->execute();
                        $updateStmt->close();

                        echo "Akun Anda telah diblokir setelah 3 kali gagal login.";
                    } else {
                        $updateStmt = $conn->prepare("UPDATE tb_transaksi SET login_attempts = ? WHERE username = ?");
                        $updateStmt->bind_param("is", $loginAttempts, $inputName);
                        $updateStmt->execute();
                        $updateStmt->close();

                        echo "Username atau PIN salah. Percobaan tersisa: " . (3 - $loginAttempts);
                    }
                }
            }
        } else {
            echo "Username tidak ditemukan.";
        }

        $stmt->close();
    } else {
        echo "Username dan PIN tidak boleh kosong.";
    }
}

$conn->close();
?>
