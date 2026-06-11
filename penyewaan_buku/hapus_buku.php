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

if($bukuClass->hapus($id)){
    header("Location: admin.php");
} else {
    echo "<script>alert('Gagal hapus buku!'); window.location='admin.php';</script>";
}
?>