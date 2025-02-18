<!DOCTYPE html>
<?php
session_start();
if (isset($_SESSION['username'])) {
    header('location: home.php');
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            height: 80vh;
            display: flex;
            align-items: center;
            background-color: #0e3c9a;
        }

        .form-login {
            max-width: 550px;
            margin: auto;
        }
        .gambar{
             max-width: 550px;
        }
        .container-fluid{
            color:#fff;
        }
        

    </style>
    <title>Login</title>
</head>

<body>
    <img class="gambar" src="bankgit.png">
    <div class="container-fluid">
        <form class="form-login" method="post" action="login.php">
            <h3 class="text-center mb-2">Masuk Akun</h3>
            <div class="form-floating mb-4">
                <input type="text" name="username" class="form-control" placeholder="username" required>
                <label>username</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <label>Password</label>
            </div>
            <button class="btn btn-primary mb-2 w-100" type="submit">Masuk</button>
            <p class="text-center">Belum Punya Akun? <a href="daftar.php">Daftar</a></p>
            <p class="text-muted text-center">&copy; BankGit</p>
        </form>
    </div>
</body>

</html>