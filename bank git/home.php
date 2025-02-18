<!doctype html>
<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('location: index.php');
}
?>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <style>
    .gambar {
    display: block;
    margin: 0 auto;
    max-width: 500px;
}

  </style>

  <title>Menu</title>
</head>

<body>
 

 
   
    </h3>
  </font>
  <?php
  include "menu.php"; 
  ?>
 



  </div>

</body>

</html>