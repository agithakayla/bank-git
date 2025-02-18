<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Koneksi ke database
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "db_atm";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Periksa koneksi database
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Ambil saldo pengguna dari tabel tb_bank
$sql = "SELECT saldo FROM tb_bank WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Error: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

// Jika user belum memiliki saldo, tambahkan saldo awal (100.000)
if ($saldo === null) {
    $saldo = 100000;
    $sql = "INSERT INTO tb_bank (username, saldo) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query Error: " . $conn->error);
    }
    $stmt->bind_param("si", $username, $saldo);
    $stmt->execute();
    $stmt->close();
}

// Cek apakah form disubmit untuk simpan atau ambil uang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($jumlah > 0) {
        $jenis = ''; // Pastikan $jenis memiliki nilai

      if ($action === 'ambil') {
            if ($jumlah > $saldo) {
                $error = "Saldo tidak mencukupi!";
            } else {
                $saldo -= $jumlah;
                $jenis = 'ambil';
            }
        }

        if (!isset($error)) {
            // Update saldo di database tb_bank
            $sql = "UPDATE tb_bank SET saldo = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Query Error: " . $conn->error);
            }
            $stmt->bind_param("is", $saldo, $username);
            if (!$stmt->execute()) {
                die("Error updating saldo: " . $conn->error);
            }
            $stmt->close();

            // Simpan transaksi ke tabel tb_transaksi
            $sql = "INSERT INTO tb_transaksi (username, jenis, jumlah) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Query Error: " . $conn->error);
            }
            $stmt->bind_param("ssi", $username, $jenis, $jumlah);
            if (!$stmt->execute()) {
                die("Error saving transaction: " . $conn->error);
            }
            $stmt->close();
            // Debugging untuk melihat saldo sebelum & sesudah transaksi
            echo "<h4>Debugging Data:</h4>";
            echo "Saldo sebelum transaksi: Rp. " . number_format($saldo, 0, ',', '.') . "<br>";
            echo "Jumlah transaksi: Rp. " . number_format($jumlah, 0, ',', '.') . "<br>";
            echo "Jenis transaksi: " . $jenis . "<br>";
            echo "Saldo setelah transaksi: Rp. " . number_format($saldo, 0, ',', '.') . "<br>";
            echo "<br><a href='menu.php'>Kembali</a>";
            exit; // Hentikan eksekusi agar tidak ada pengiriman ulang
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Tabungan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0e3c9a;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="fw-bold">Bank Tabungan ATM</h2>
                    </div>
                    <div class="card-body">
                        <!-- Tampilkan pesan error jika ada -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Saldo -->
                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Saldo Saat Ini</h4>
                            <h2 class="text-success fw-bold">
                                Rp. <?= number_format($saldo, 0, ',', '.'); ?>
                            </h2>
                        </div>

                        <!-- Form untuk simpan dan ambil uang -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah Uang (Rp)</label>
                                <input type="number" id="jumlah" name="jumlah" class="form-control" placeholder="Masukkan jumlah uang" min="1" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="action" value="ambil" class="btn btn-danger">Ambil Uang</button>
                            </div>
                        </form>

                        <!-- Menu navigasi -->
                        <div class="mt-4 text-center">
                            <a href="home.php" class="btn btn-outline-light">Menu Utama</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
