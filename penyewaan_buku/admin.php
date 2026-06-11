<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit;
}

$buku = $bukuClass->getAll();
$transaksi = $transaksiClass->getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | Sewa Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h2>📚 ADMIN PANEL - SEWA BUKU</h2>
            <div class="nav-links">
                <span>Halo, <b><?= $_SESSION['username'] ?></b></span>
                <a href="logout.php" class="btn logout">Logout</a>
            </div>
        </div>

        <!-- MANAJEMEN BUKU -->
        <div class="card-section">
            <h3>📖 Manajemen Buku</h3>
            <a href="tambah_buku.php" class="btn btn-primary">+ Tambah Buku</a>
            
            <div class="table-responsive">
                <table border="1" cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Harga/Hari</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($buku as $b): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($b['judul']) ?></td>
                            <td><?= htmlspecialchars($b['penulis']) ?></td>
                            <td><?= htmlspecialchars($b['penerbit']) ?></td>
                            <td><?= $b['tahun'] ?></td>
                            <td><?= $b['stok'] ?></td>
                            <td>Rp <?= number_format($b['harga_sewa_per_hari'],0,',','.') ?></td>
                            <td>
                                <a href="edit_buku.php?id=<?= $b['id'] ?>" class="btn-edit">Edit</a>
                                <a href="hapus_buku.php?id=<?= $b['id'] ?>" class="btn-hapus" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIWAYAT TRANSAKSI -->
        <div class="card-section">
            <h3>📋 Riwayat Transaksi Penyewaan</h3>
            <?php if(empty($transaksi)): ?>
                <p style="text-align: center; padding: 20px;">Belum ada transaksi.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table border="1" cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Buku</th>
                            <th>Tgl Sewa</th>
                            <th>Tgl Harus Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Denda</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($transaksi as $t): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($t['username']) ?></td>
                            <td><?= htmlspecialchars($t['judul']) ?></td>
                            <td><?= $t['tanggal_sewa'] ?></td>
                            <td><?= $t['tanggal_kembali'] ?></td>
                            <td><?= $t['tanggal_pengembalian'] ?: '-' ?></td>
                            <td>Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
                            <td>Rp <?= number_format($t['denda'],0,',','.') ?></td>
                            <td>Rp <?= number_format($t['denda'],0,',','.') ?></td>
                            <td>
                                <?php if($t['status'] == 'pending'): ?>
                                    <span class="status-pending">⏳ Pending</span>
                                <?php elseif($t['status'] == 'disewa'): ?>
                                    <span class="status-sewa">📖 Disewa</span>
                                <?php elseif($t['status'] == 'dikembalikan'): ?>
                                    <span class="status-kembali">✅ Dikembalikan</span>
                                <?php else: ?>
                                    <span class="status-terlambat">⚠️ Terlambat</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($t['status'] == 'pending'): ?>
                                    <a href="konfirmasi_sewa.php?id=<?= $t['id'] ?>" class="btn-konfirm" onclick="return confirm('Konfirmasi penyewaan ini?')">Konfirmasi Sewa</a>
                                <?php elseif($t['status'] == 'disewa'): ?>
                                    <a href="konfirmasi.php?id=<?= $t['id'] ?>" class="btn-konfirm" onclick="return confirm('Konfirmasi pengembalian?')">Konfirmasi Kembali</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .status-pending {
            background: #f39c12;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .status-sewa {
            background: #3498db;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .status-kembali {
            background: #27ae60;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .status-terlambat {
            background: #e74c3c;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .btn-konfirm {
            background: #3498db;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-konfirm:hover {
            background: #2980b9;
        }
    </style>
</body>
</html>