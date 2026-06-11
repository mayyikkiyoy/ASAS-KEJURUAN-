<?php
session_start();
require 'functions.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] != 'user'){
    header("Location: index.php");
    exit;
}

$buku = $bukuClass->getAll();
$riwayat = $transaksiClass->getByUser($_SESSION['id_user']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User | Sewa Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h2>📚 SEWA BUKU ONLINE</h2>
            <div class="nav-links">
                <span>Halo, <b><?= $_SESSION['username'] ?></b></span>
                <a href="logout.php" class="btn logout">Logout</a>
            </div>
        </div>

        <!-- DAFTAR BUKU TERSEDIA -->
        <div class="card-section">
            <h3>📖 Daftar Buku Tersedia</h3>
            <div class="buku-grid">
                <?php foreach($buku as $b): ?>
                <div class="buku-card">
                    <h4><?= htmlspecialchars($b['judul']) ?></h4>
                    <p>Penulis: <?= htmlspecialchars($b['penulis']) ?></p>
                    <p>Penerbit: <?= htmlspecialchars($b['penerbit']) ?></p>
                    <p>Tahun: <?= $b['tahun'] ?></p>
                    <p>Stok: <?= $b['stok'] ?></p>
                    <p>Harga/hari: Rp <?= number_format($b['harga_sewa_per_hari'],0,',','.') ?></p>
                    <?php if($b['stok'] > 0): ?>
                        <a href="sewa.php?id=<?= $b['id'] ?>" class="btn-sewa">Sewa Buku</a>
                    <?php else: ?>
                        <button class="btn-habis" disabled>Stok Habis</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIWAYAT PEMINJAMAN -->
        <div class="card-section">
            <h3>📋 Riwayat Peminjaman Saya</h3>
            <?php if(empty($riwayat)): ?>
                <p style="text-align: center; padding: 20px;">Belum ada riwayat peminjaman.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table border="1" cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Harus Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Total Harga</th>
                            <th>Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach($riwayat as $r): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($r['judul']) ?></td>
                            <td><?= $r['tanggal_sewa'] ?></td>
                            <td><?= $r['tanggal_kembali'] ?></td>
                            <td><?= $r['tanggal_pengembalian'] ?: '-' ?></td>
                            <td>Rp <?= number_format($r['total_harga'],0,',','.') ?></td>
                            <td>Rp <?= number_format($r['denda'],0,',','.') ?></td>
                            <td>Rp <?= number_format($r['denda'],0,',','.') ?></td>
                            <td>
                                <?php if($r['status'] == 'pending'): ?>
                                    <span class="status-pending">⏳ Menunggu Konfirmasi</span>
                                <?php elseif($r['status'] == 'disewa'): ?>
                                    <span class="status-sewa">📖 Sedang Dipinjam</span>
                                <?php elseif($r['status'] == 'dikembalikan'): ?>
                                    <span class="status-kembali">✅ Selesai</span>
                                <?php else: ?>
                                    <span class="status-terlambat">⚠️ Terlambat</span>
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
        .btn-sewa {
            background: #27ae60;
            padding: 8px 15px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-sewa:hover {
            background: #219a52;
        }
        .btn-habis {
            background: #95a5a6;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            color: white;
            margin-top: 10px;
            cursor: not-allowed;
        }
    </style>
</body>
</html>