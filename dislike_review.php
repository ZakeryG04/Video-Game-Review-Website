<?php
session_start();
require_once("SessionManager.php");

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit;
}

$config = include("config.php");
$pdo = new PDO(
    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        UPDATE review
        SET dislikes = dislikes + 1
        WHERE review_id = :review_id
    ");

    $stmt->bindParam(":review_id", $review_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: view_reviews.php");
    exit();
} else {
    header("Location: view_reviews.php");
    exit();
}
