<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$config = include 'config.php';


try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['game_name']) && !empty($_POST['game_platform'])) {
    $game_name = trim($_POST['game_name']);
    $game_platform = trim($_POST['game_platform']);

    $updateStmt = $pdo->prepare("
        UPDATE backlog
        SET date_started = NOW()
        WHERE user_id = :user_id AND game_name = :game_name AND game_platform = :game_platform
    ");

    $updateStmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $updateStmt->bindParam(":game_name", $game_name, PDO::PARAM_STR);
    $updateStmt->bindParam(":game_platform", $game_platform, PDO::PARAM_STR);

    $updateStmt->execute();

    // Redirect back to backlog page after updating
    header("Location: backlog.php");
    exit();
} else {
    // Redirect back if POST data is missing
    header("Location: backlog.php");
    exit();
}
