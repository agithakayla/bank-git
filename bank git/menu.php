<?php
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
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



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Menu ATM</title>
  <style>
        body {
            background-color: #0e3c9a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .gambar {
            max-width: 550px;
            display: block;
            margin-bottom: 20px; /* Jarak antara logo dan menu */
        }

        section {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        aside {
            border: 1px solid white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            width: 250px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
        }

        aside:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        a {
            text-decoration: none;
            color: white;
            display: block;
        }
    </style>
</head>
<body>

<header>
    <img class="gambar" src="bankgit.png" alt="Logo Bank">
    <h3 style="color: white;">Selamat Datang, <?=($_SESSION['username']); ?>!</h3>
    <div class="text-center mb-4">
                            <h4 class="fw-bold">Saldo Saat Ini</h4>
                            <h2 class="text-success fw-bold">
                                Rp. <?= number_format($saldo, 0, ',', '.'); ?>
                            </h2>
                        </div>

</header>

</body>
</html>






<section>
        <a href="transfer.php">
            <aside>
                <h4>Transfer</h4>
            </aside>
        </a>

        <a href="debit.php">
            <aside>
                <h4>Ambil Uang (Debit)</h4>
            </aside>
        </a>

        <a href="kredit.php">
            <aside>
                <h4>Simpan Uang (Kredit)</h4>
            </aside>
        </a>

        <a href="ubah.php">
            <aside>
                <h4>Ubah Pin</h4>
            </aside>
        </a>

        <a href="keluar.php">
            <aside>
                <h4>Keluar</h4>
            </aside>
        </a>

    </section>