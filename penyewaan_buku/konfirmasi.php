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
$transaksi = $transaksiClass->getByIdWithDetail($id);

if(!$transaksi){
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='admin.php';</script>";
    exit;
}

$denda_per_hari = 2000;
$tanggal_jatuh_tempo = $transaksi['tanggal_kembali'];
$tanggal_sekarang = date('Y-m-d');

// Hitung telat jika ada
$telat = 0;
if($tanggal_sekarang > $tanggal_jatuh_tempo){
    $tgl_kembali = new DateTime($tanggal_jatuh_tempo);
    $tgl_sekarang = new DateTime($tanggal_sekarang);
    $telat = $tgl_kembali->diff($tgl_sekarang)->days;
}

if(isset($_POST['konfirmasi'])){
    $status = $_POST['status'];
    $tanggal_pengembalian = date('Y-m-d');
    $denda_final = 0;
    
    if($status == 'tepat_waktu'){
        $denda_final = 0;
    } else {
        $tanggal_kembali_admin = $_POST['tanggal_kembali'];
        $tgl_kembali_admin = new DateTime($tanggal_kembali_admin);
        $tgl_jatuh_tempo = new DateTime($tanggal_jatuh_tempo);
        
        if($tgl_kembali_admin > $tgl_jatuh_tempo){
            $selisih = $tgl_jatuh_tempo->diff($tgl_kembali_admin)->days;
            $denda_final = $selisih * $denda_per_hari;
        }
        $tanggal_pengembalian = $tanggal_kembali_admin;
    }
    
    $sql = "UPDATE transaksi SET 
            tanggal_pengembalian='$tanggal_pengembalian', 
            denda=$denda_final, 
            status='dikembalikan' 
            WHERE id=$id";
    
    if(mysqli_query($conn, $sql)){
        $bukuClass->tambahStok($transaksi['id_buku']);
        echo "<script>alert('Pengembalian berhasil dikonfirmasi!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal konfirmasi pengembalian!'); window.location='admin.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pengembalian | Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>📖 Konfirmasi Pengembalian Buku</h2>
        
        <div class="info-transaksi">
            <p><strong>User:</strong> <?= htmlspecialchars($transaksi['username']) ?></p>
            <p><strong>Buku:</strong> <?= htmlspecialchars($transaksi['judul']) ?></p>
            <p><strong>Tanggal Sewa:</strong> <?= $transaksi['tanggal_sewa'] ?></p>
            <p><strong>Tanggal Harus Kembali:</strong> <?= $transaksi['tanggal_kembali'] ?></p>
            <p><strong>Status:</strong> 
                <?php if($telat > 0): ?>
                    <span class="status-terlambat">⚠️ Terlambat <?= $telat ?> hari</span>
                <?php else: ?>
                    <span class="status-sewa">📖 Masih dalam masa sewa</span>
                <?php endif; ?>
            </p>
        </div>

        <form method="post">
            <div class="form-group">
                <label>Status Pengembalian:</label>
                <select name="status" id="status" required onchange="toggleTanggal()">
                    <option value="tepat_waktu">✅ Tepat Waktu (Tidak Ada Denda)</option>
                    <option value="terlambat">⚠️ Terlambat (Kena Denda)</option>
                </select>
            </div>

            <div class="form-group" id="tanggal_group" style="display: none;">
                <label>Tanggal Pengembalian:</label>
                <input type="date" name="tanggal_kembali" id="tanggal_kembali" value="<?= date('Y-m-d') ?>">
                <small>Pilih tanggal buku benar-benar dikembalikan</small>
            </div>

            <div class="info-denda" id="info_denda" style="display: none;">
                <p><strong>Denda per hari:</strong> Rp <?= number_format($denda_per_hari,0,',','.') ?></p>
                <p id="denda_hitung">Denda: Rp 0</p>
            </div>

            <button type="submit" name="konfirmasi">Konfirmasi Pengembalian</button>
        </form>
        <a href="admin.php">Kembali</a>
    </div>

    <script>
        function toggleTanggal() {
            var status = document.getElementById('status').value;
            var tanggalGroup = document.getElementById('tanggal_group');
            var infoDenda = document.getElementById('info_denda');
            
            if(status == 'terlambat'){
                tanggalGroup.style.display = 'block';
                infoDenda.style.display = 'block';
                hitungDenda();
            } else {
                tanggalGroup.style.display = 'none';
                infoDenda.style.display = 'none';
            }
        }
        
        function hitungDenda() {
            var tanggalKembali = document.getElementById('tanggal_kembali').value;
            var tanggalJatuhTempo = '<?= $transaksi['tanggal_kembali'] ?>';
            var dendaPerHari = <?= $denda_per_hari ?>;
            
            if(tanggalKembali && tanggalKembali > tanggalJatuhTempo){
                var tglKembali = new Date(tanggalKembali);
                var tglJatuh = new Date(tanggalJatuhTempo);
                var selisih = Math.ceil((tglKembali - tglJatuh) / (1000 * 60 * 60 * 24));
                var denda = selisih * dendaPerHari;
                document.getElementById('denda_hitung').innerHTML = 'Denda: Rp ' + denda.toLocaleString('id-ID');
            } else {
                document.getElementById('denda_hitung').innerHTML = 'Denda: Rp 0 (Tidak terlambat)';
            }
        }
        
        document.getElementById('tanggal_kembali')?.addEventListener('change', hitungDenda);
    </script>

    <style>
        .info-transaksi {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-transaksi p {
            margin: 5px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .info-denda {
            background: #fee;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #e74c3c;
        }
        .status-terlambat {
            background: #e74c3c;
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
    </style>
</body>
</html>