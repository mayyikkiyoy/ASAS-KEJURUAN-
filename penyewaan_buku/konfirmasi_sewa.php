<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<script>alert('ID transaksi tidak ditemukan!'); window.location='admin.php';</script>";
    exit;
}

$id = $_GET['id'];

$result = $transaksiClass->konfirmasiSewa($id);

if($result === true){
    echo "<script>alert('Penyewaan berhasil dikonfirmasi! Stok berkurang.'); window.location='admin.php';</script>";
} else {
    echo "<script>alert('$result'); window.location='admin.php';</script>";
}
?>