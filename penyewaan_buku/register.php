<?php
require 'functions.php';

if(isset($_POST['register'])){
    $result = $userClass->register(
        $_POST['username'],
        $_POST['email'],
        $_POST['no_hp'],
        $_POST['alamat'],
        $_POST['password'],
        $_POST['password2']
    );
    
    if($result === true){
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('$result');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Sewa Buku Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>📚 REGISTER</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="no_hp" placeholder="No HP" required>
            <textarea name="alamat" placeholder="Alamat" rows="3" required></textarea>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="password2" placeholder="Konfirmasi Password" required>
            <button type="submit" name="register">Daftar</button>
        </form>
        <a href="index.php">Sudah punya akun? Login</a>
    </div>
</body>
</html>