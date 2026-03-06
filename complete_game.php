<?php

session_start();

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

$config = include 'config.php';

$pdo = new PDO(
    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_name'], $_POST['game_platform'])) {
    $game_name = $_POST['game_name'];
    $game_platform = $_POST['game_platform'];

    $completeStmt = $pdo->prepare("
        UPDATE backlog
        SET date_completed = NOW()
        WHERE user_id = :user_id AND game_name = :game_name AND game_platform = :game_platform
    ");

    $completeStmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $completeStmt->bindParam(":game_name", $game_name, PDO::PARAM_STR);
    $completeStmt->bindParam(":game_platform", $game_platform, PDO::PARAM_STR);
    $completeStmt->execute();

    header("Location: backlog.php");
    exit();
}
