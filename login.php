<?php
require_once("SessionManager.php");

session_start(); // make sure session is started

$config = include 'config.php';
$pdo = new PDO(
    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$currentSession = new SessionManager($pdo);
$errorAlert = "";

if ($currentSession->isLoggedIn()) {
    header("Location: backlog.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($currentSession->authenticate($email, $password)) {
        header("Location: backlog.php");
        exit();
    } else {
        $errorAlert = "Invalid email or password.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>

    <link rel="stylesheet" href="style.css">
</head>
<body id="LoginBody">
<h1 id="LoginTitle"> Login  </h1>
<?php if($errorAlert) echo "<h2 style ='color:red;'>$errorAlert</h2>" ?>
<form method="post" action="" id = "LoginForm">
    <h3>
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <input type ="submit" value="Login">
    </h3>

<p>Don't have an account? <a class="button" href="register.html">Register</a></p>

</form>




</body>



</html>
