<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

if(isset($_POST['submit'])){
    $result = $bukuClass->tambah(
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
        echo "<script>alert('Gagal tambah buku!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku | Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>📖 Tambah Buku</h2>
        <form method="post">
            <input type="text" name="judul" placeholder="Judul Buku" required>
            <input type="text" name="penulis" placeholder="Penulis" required>
            <input type="text" name="penerbit" placeholder="Penerbit" required>
            <input type="number" name="tahun" placeholder="Tahun Terbit" required>
            <input type="number" name="stok" placeholder="Stok" value="1" required>
            <input type="number" name="harga_sewa_per_hari" placeholder="Harga Sewa/Hari" required>
            <button type="submit" name="submit">Tambah Buku</button>
        </form>
        <a href="admin.php">Kembali</a>
    </div>
</body>
</html>