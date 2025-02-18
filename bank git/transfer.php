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

// Ambil saldo pengguna
$sql = "SELECT saldo FROM tb_bank WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

// Cek apakah form disubmit untuk transfer uang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;
    $no_rek = isset($_POST['no_rek']) ? $_POST['no_rek'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($jumlah > 0 && !empty($no_rek) && $action === 'transfer') {
        // Periksa apakah saldo cukup
        if ($jumlah > $saldo) {
            $error = "Saldo tidak mencukupi!";
        } else {
            // Periksa apakah rekening tujuan ada
            $sql = "SELECT saldo FROM tb_transaksi WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $no_rek);
            $stmt->execute();
            $stmt->bind_result($saldo_penerima);
            $stmt->fetch();
            $stmt->close();

            if ($saldo_penerima === null) {
                $error = "Nomor rekening tujuan tidak ditemukan!";
            } else {
                // *Kurangi saldo pengirim*
                $sql = "UPDATE tb_bank SET saldo = saldo - ? WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $jumlah, $username);
                $stmt->execute();
                $stmt->close();

                // *Tambah saldo penerima*
                $sql = "UPDATE tb_transaksi SET saldo = saldo + ? WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $jumlah, $no_rek);
                $stmt->execute();
                $stmt->close();

                // *Simpan transaksi*
                $sql = "INSERT INTO tb_transaksi (username, jenis, jumlah, no_rek) VALUES (?, 'transfer', ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $username, $jumlah, $no_rek);
                $stmt->execute();
                $stmt->close();

                // *Ambil saldo penerima setelah update*
                $sql = "SELECT saldo FROM tb_bank WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $no_rek);
                $stmt->execute();
                $stmt->bind_result($saldo_penerima_setelah);
                $stmt->fetch();
                $stmt->close();

                // *Ambil total saldo dalam sistem setelah transfer*
                $sql = "SELECT SUM(saldo) FROM tb_bank";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $stmt->bind_result($total_saldo);
                $stmt->fetch();
                $stmt->close();

                // *Tampilkan hasil transfer*
                echo "<h4>Transfer Berhasil!</h4>";
                echo "Pengirim: <strong>$username</strong><br>";
                echo "Penerima: <strong>$no_rek</strong><br>";
                echo "Jumlah Transfer: Rp. " . number_format($jumlah, 0, ',', '.') . "<br>";
                echo "Saldo Setelah Transfer: Rp. " . number_format($saldo - $jumlah, 0, ',', '.') . "<br>";
                echo "Saldo Penerima Setelah Transfer: Rp. " . number_format($saldo_penerima_setelah, 0, ',', '.') . "<br>";
                echo "<br><a href='transfer.php'>Kembali</a>";
                exit;
            }
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
    <title>Transfer Uang</title>
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
                        <h2 class="fw-bold">Transfer Uang</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Saldo Saat Ini</h4>
                            <h2 class="text-success fw-bold">
                                Rp. <?= number_format($saldo, 0, ',', '.'); ?>
                            </h2>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="no_rek" class="form-label">Nomor Rekening Tujuan</label>
                                <input type="text" id="no_rek" name="no_rek" class="form-control" placeholder="Masukkan No Rekening Tujuan" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah Transfer (Rp)</label>
                                <input type="number" id="jumlah" name="jumlah" class="form-control" placeholder="Masukkan jumlah transfer" min="1" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="action" value="transfer" class="btn btn-primary">Transfer Uang</button>
                            </div>
                        </form>

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