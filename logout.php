<?php
session_start();
$_SESSION = []; // Kosongkan array session
session_unset(); // Hapus variabel session
session_destroy(); // Hancurkan file session di server
header("Location: index.php");
exit;
?>