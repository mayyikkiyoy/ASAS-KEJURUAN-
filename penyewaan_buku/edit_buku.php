<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

// Cek apakah id ada di URL
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: admin.php");
    exit;
}

$id = $_GET['id'];
$buku = $bukuClass->getById($id);

// Cek apakah buku ditemukan
if(!$buku){
    header("Location: admin.php");
    exit;
}

if(isset($_POST['submit'])){
    $result = $bukuClass->edit(
        $id,
        $_POST['judul'],
        $_POST['penulis'],
        $_POST['penerbit'],
        $_POST['tahun'],
        $_POST['stok'],
        $_POST['harga_sewa_per_hari']
    );
    
    if($result){
        header("Location: admin.php");
        exit;
    } else {
        echo "<script>alert('Gagal edit buku!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku | Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>✏️ Edit Buku</h2>
        <form method="post">
            <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required>
            <input type="text" name="penulis" value="<?= htmlspecialchars($buku['penulis']) ?>" required>
            <input type="text" name="penerbit" value="<?= htmlspecialchars($buku['penerbit']) ?>" required>
            <input type="number" name="tahun" value="<?= $buku['tahun'] ?>" required>
            <input type="number" name="stok" value="<?= $buku['stok'] ?>" required>
            <input type="number" name="harga_sewa_per_hari" value="<?= $buku['harga_sewa_per_hari'] ?>" required>
            <button type="submit" name="submit">Update Buku</button>
        </form>
        <a href="admin.php">Kembali</a>
    </div>
</body>
</html>