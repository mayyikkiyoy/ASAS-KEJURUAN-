<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Sewa Buku Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>📚 SEWA BUKU ONLINE</h2>
        <h3>Login</h3>
        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <a href="register.php">Belum punya akun? Register</a>
    </div>
</body>
</html>