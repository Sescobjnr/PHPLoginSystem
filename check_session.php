<?php
session_start();

// Redirect if not logged in
if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>
