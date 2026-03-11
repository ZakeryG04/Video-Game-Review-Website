<?php
$config = include 'config.php';


$name = $_POST['name'];
$password = $_POST['password'];
$email = $_POST['email'];

$hashPasword = password_hash($password, PASSWORD_DEFAULT);

//pdo connection
try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO user (name, password_hash, email) VALUES (:name, :password_hash, :email)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':password_hash', $hashPasword);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo "Registration successful!";
    echo "<p><a class='button' href='login.php'>Go to Login</a></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
