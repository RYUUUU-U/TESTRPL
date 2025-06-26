<?php
session_start();

// Hapus semua data session
$_SESSION = array();
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>