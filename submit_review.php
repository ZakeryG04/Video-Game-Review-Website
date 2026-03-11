<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"])) {
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

// Fetch games from user's backlog
$statement = $pdo->prepare("
    SELECT game_name, game_platform
    FROM backlog
    WHERE user_id = :user_id
    ORDER BY date_created DESC
");
$statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$statement->execute();
$games = $statement->fetchAll(PDO::FETCH_ASSOC);

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game'], $_POST['review'], $_POST['rating'])) {
    // Split the combined value back into game_name and game_platform
    list($game_name, $game_platform) = explode('|', $_POST['game']);

    $review_content = trim($_POST['review']);
    $stars = (int)$_POST['rating'];

    $reviewStmt = $pdo->prepare("
        INSERT INTO review (user_id, game_name, game_platform, content, stars, date_submitted)
        VALUES (:user_id, :game_name, :game_platform, :content, :stars, NOW())
    ");

    $reviewStmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $reviewStmt->bindParam(":game_name", $game_name, PDO::PARAM_STR);
    $reviewStmt->bindParam(":game_platform", $game_platform, PDO::PARAM_STR);
    $reviewStmt->bindParam(":content", $review_content, PDO::PARAM_STR);
    $reviewStmt->bindParam(":stars", $stars, PDO::PARAM_INT);

    $reviewStmt->execute();

    header("Location: backlog.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="submit-review-body">
    <h1>Submit Review for a Game</h1>
    <form method="post" action="">
        <label for="game">Game:</label>
        <select name="game" id="game" required>
            <?php foreach ($games as $game): ?>
                <option value="<?php echo htmlspecialchars($game['game_name'] . '|' . $game['game_platform']); ?>">
                    <?php echo htmlspecialchars($game['game_name'] . " (" . $game['game_platform'] . ")"); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="review">Review:</label><br>
        <textarea name="review" id="review" rows="5" cols="50" required></textarea><br><br>

        <label for="rating">Rating:</label>
        <select name="rating" id="rating" required>
            <option value="0">0 Stars</option>
            <option value="1">1 Star</option>
            <option value="2">2 Stars</option>
            <option value="3">3 Stars</option>
            <option value="4">4 Stars</option>
            <option value="5">5 Stars</option>
        </select><br><br>

        <input type="submit" value="Submit Review">
        <a href="backlog.php"> Go back</a>
    </form>
</body>
</html>
