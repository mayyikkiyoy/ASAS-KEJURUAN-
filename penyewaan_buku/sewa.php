<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'user'){
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: user.php");
    exit;
}

$id_buku = $_GET['id'];

if(isset($_POST['sewa'])){
    $lama_hari = $_POST['lama_hari'];
    $result = $transaksiClass->sewa($_SESSION['id_user'], $id_buku, $lama_hari);
    
    if($result === true){
        echo "<script>alert('Permintaan sewa berhasil! Silakan tunggu konfirmasi admin.'); window.location='user.php';</script>";
    } else {
        echo "<script>alert('$result'); window.location='user.php';</script>";
    }
    exit;
}

$buku = $bukuClass->getById($id_buku);

if(!$buku){
    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sewa Buku | User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>📖 Sewa Buku</h2>
        <h3><?= htmlspecialchars($buku['judul']) ?></h3>
        <p>Penulis: <?= htmlspecialchars($buku['penulis']) ?></p>
        <p>Harga per hari: Rp <?= number_format($buku['harga_sewa_per_hari'],0,',','.') ?></p>
        <p>Stok tersedia: <?= $buku['stok'] ?></p>
        <form method="post">
            <label>Lama Sewa (hari):</label>
            <input type="number" name="lama_hari" min="1" max="30" required>
            <button type="submit" name="sewa">Ajukan Sewa</button>
        </form>
        <a href="user.php">Kembali</a>
    </div>
</body>
</html>