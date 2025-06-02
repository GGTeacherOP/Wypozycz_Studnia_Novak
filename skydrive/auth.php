<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

include '../includes/config.php';
?>