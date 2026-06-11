<?php
$conn = mysqli_connect("localhost", "root", "", "penyewaan_buku");

if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "penyewaan_buku";
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            die("Koneksi gagal: " . $this->conn->connect_error);
        }
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function getConn() {
        return $this->conn;
    }
}

class User {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function register($username, $email, $no_hp, $alamat, $password, $password2) {
        if ($password !== $password2) {
            return "Konfirmasi password tidak sesuai!";
        }
        
        $password = md5($password);
        $check = $this->db->query("SELECT * FROM users WHERE username='$username'");
        if ($check->num_rows > 0) {
            return "Username sudah digunakan!";
        }
        
        $sql = "INSERT INTO users (username, email, no_hp, alamat, password, role) 
                VALUES ('$username', '$email', '$no_hp', '$alamat', '$password', 'user')";
        
        if ($this->db->query($sql)) {
            return true;
        }
        return "Gagal registrasi!";
    }

    public function login($username, $password) {
        $password = md5($password);
        $result = $this->db->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }
}

class Buku {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM buku ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $id = intval($id);
        $result = $this->db->query("SELECT * FROM buku WHERE id = $id");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function tambah($judul, $penulis, $penerbit, $tahun, $stok, $harga_sewa_per_hari) {
        $sql = "INSERT INTO buku (judul, penulis, penerbit, tahun, stok, harga_sewa_per_hari) 
                VALUES ('$judul', '$penulis', '$penerbit', $tahun, $stok, $harga_sewa_per_hari)";
        return $this->db->query($sql);
    }

    public function edit($id, $judul, $penulis, $penerbit, $tahun, $stok, $harga_sewa_per_hari) {
        $sql = "UPDATE buku SET 
                judul='$judul', 
                penulis='$penulis', 
                penerbit='$penerbit', 
                tahun=$tahun, 
                stok=$stok, 
                harga_sewa_per_hari=$harga_sewa_per_hari 
                WHERE id=$id";
        return $this->db->query($sql);
    }

    public function hapus($id) {
        return $this->db->query("DELETE FROM buku WHERE id=$id");
    }

    public function kurangiStok($id_buku, $jumlah = 1) {
        $id_buku = intval($id_buku);
        return $this->db->query("UPDATE buku SET stok = stok - $jumlah WHERE id = $id_buku AND stok >= $jumlah");
    }

    public function tambahStok($id_buku, $jumlah = 1) {
        $id_buku = intval($id_buku);
        return $this->db->query("UPDATE buku SET stok = stok + $jumlah WHERE id = $id_buku");
    }
}

class Transaksi {
    private $db;
    const DENDA_PER_HARI = 2000;

    public function __construct($database) {
        $this->db = $database;
    }

    // USER SEWA (status pending)
    public function sewa($id_user, $id_buku, $lama_hari) {
        $buku = new Buku($this->db);
        $dataBuku = $buku->getById($id_buku);
        
        if (!$dataBuku || $dataBuku['stok'] < 1) {
            return "Stok buku habis!";
        }
        
        $tanggal_sewa = date('Y-m-d');
        $tanggal_kembali = date('Y-m-d', strtotime("+$lama_hari days"));
        $total_harga = $dataBuku['harga_sewa_per_hari'] * $lama_hari;
        
        $sql = "INSERT INTO transaksi (id_user, id_buku, tanggal_sewa, tanggal_kembali, total_harga, status) 
                VALUES ($id_user, $id_buku, '$tanggal_sewa', '$tanggal_kembali', $total_harga, 'pending')";
        
        if ($this->db->query($sql)) {
            return true;
        }
        return "Gagal menyewa!";
    }

    // ADMIN KONFIRMASI SEWA
    public function konfirmasiSewa($id_transaksi) {
        $id_transaksi = intval($id_transaksi);
        $transaksi = $this->getById($id_transaksi);
        
        if (!$transaksi) {
            return "Transaksi tidak ditemukan!";
        }
        
        if ($transaksi['status'] != 'pending') {
            return "Transaksi sudah dikonfirmasi!";
        }
        
        // Kurangi stok
        $buku = new Buku($this->db);
        $buku->kurangiStok($transaksi['id_buku']);
        
        $sql = "UPDATE transaksi SET status='disewa' WHERE id = $id_transaksi";
        
        if ($this->db->query($sql)) {
            return true;
        }
        return "Gagal konfirmasi sewa!";
    }

    // ADMIN KONFIRMASI KEMBALI
    public function konfirmasiKembali($id_transaksi) {
        $id_transaksi = intval($id_transaksi);
        $transaksi = $this->getById($id_transaksi);
        
        if (!$transaksi) {
            return "Transaksi tidak ditemukan!";
        }
        
        if ($transaksi['status'] != 'disewa') {
            return "Transaksi belum disewa!";
        }
        
        $tanggal_pengembalian = date('Y-m-d');
        $denda = 0;
        
        if ($tanggal_pengembalian > $transaksi['tanggal_kembali']) {
            $tgl_kembali = new DateTime($transaksi['tanggal_kembali']);
            $tgl_pengembalian = new DateTime($tanggal_pengembalian);
            $selisih_hari = $tgl_kembali->diff($tgl_pengembalian)->days;
            $denda = $selisih_hari * self::DENDA_PER_HARI;
        }
        
        $sql = "UPDATE transaksi SET 
                tanggal_pengembalian='$tanggal_pengembalian', 
                denda=$denda, 
                status='dikembalikan' 
                WHERE id = $id_transaksi";
        
        if ($this->db->query($sql)) {
            $buku = new Buku($this->db);
            $buku->tambahStok($transaksi['id_buku']);
            return true;
        }
        return "Gagal konfirmasi pengembalian!";
    }

    public function getById($id) {
        $id = intval($id);
        $result = $this->db->query("SELECT * FROM transaksi WHERE id = $id");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function getAll() {
        $result = $this->db->query("SELECT t.*, u.username, b.judul 
                                    FROM transaksi t 
                                    JOIN users u ON t.id_user = u.id 
                                    JOIN buku b ON t.id_buku = b.id 
                                    ORDER BY t.id DESC");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function getByUser($id_user) {
        $id_user = intval($id_user);
        $result = $this->db->query("SELECT t.*, b.judul, b.penulis, b.harga_sewa_per_hari
                                    FROM transaksi t 
                                    JOIN buku b ON t.id_buku = b.id 
                                    WHERE t.id_user = $id_user 
                                    ORDER BY t.id DESC");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function updateStatusTerlambat() {
        $today = date('Y-m-d');
        $sql = "UPDATE transaksi SET status='terlambat' 
                WHERE status='disewa' AND tanggal_kembali < '$today'";
        return $this->db->query($sql);
    }
}

// Inisialisasi
$database = new Database();
$userClass = new User($database);
$bukuClass = new Buku($database);
$transaksiClass = new Transaksi($database);
$conn = $database->getConn();

// Update status terlambat
$transaksiClass->updateStatusTerlambat();
?>