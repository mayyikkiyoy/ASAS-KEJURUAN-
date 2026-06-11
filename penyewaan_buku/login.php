<?php
session_start();
require 'functions.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user = $userClass->login($username, $password);
    
    if($user){
        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'admin'){
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit;
    } else {
        echo "<script>alert('Login gagal! Username atau password salah.'); window.location='index.php';</script>";
    }
}
?>