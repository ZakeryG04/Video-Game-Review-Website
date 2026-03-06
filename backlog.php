<?php

session_start();
require_once("SessionManager.php");

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    SessionManager::logout();
    exit();
}

$config = include 'config.php';

$pdo = new PDO(
    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$user_id = $_SESSION['user_id'];

$statement = $pdo->prepare("
    SELECT game_name, game_platform, date_created, date_started, date_completed
    FROM backlog
    WHERE user_id = :user_id
    ORDER BY date_created DESC
");

$statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$statement->execute();

$games = $statement->fetchAll();

//add game form submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_name = $_POST['game_name'] ?? '';
    $game_platform = $_POST['game_platform'] ?? '';

    $insertStmt = $pdo->prepare("
        INSERT INTO backlog (user_id, game_name, game_platform, date_created)
        VALUES (:user_id, :game_name, :game_platform, NOW())
    ");

    $insertStmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $insertStmt->bindParam(":game_name", $game_name, PDO::PARAM_STR);
    $insertStmt->bindParam(":game_platform", $game_platform, PDO::PARAM_STR);
    $insertStmt->execute();

    // Refresh the page to show the new game in the list
    header("Location: backlog.php");
    exit();
}

// Filtering handling
$filter = $_GET['filter'] ?? 'all';

$sql = '
    SELECT game_name, game_platform, date_created, date_started, date_completed
    FROM backlog
    WHERE user_id = :user_id';

if ($filter === 'not_started') {
    $sql .= ' AND date_started IS NULL';
} elseif ($filter === 'in_progress') {
    $sql .= ' AND date_started IS NOT NULL AND date_completed IS NULL';
} elseif ($filter === 'completed') {
    $sql .= ' AND date_completed IS NOT NULL';
}

$sql .= ' ORDER BY date_created DESC';

$statement = $pdo->prepare($sql);
$statement->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$statement->execute();

$games = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backlog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: beige;">
    <h1 id="backlog-title">Your Game Backlog</h1>

    <div id = "top-bar">

    <form action="submit_review.php" method="get">
    <button type="submit" class="button">Submit a Review</button>
    </form>

    <form action="view_reviews.php" method="get">
    <button type="submit" class="button">View Reviews</button>
    </form>

    <!-- Filter -->
    <form method="get" action="">
        <label for="filter">Filter by Status:</label>
        <select id="filter" name="filter" onchange="this.form.submit()">
            <option value="all" <?php if (!isset($_GET['filter']) || $_GET['filter'] === 'all') echo 'selected'; ?>>All</option>
            <option value="not_started" <?php if (isset($_GET['filter']) && $_GET['filter'] === 'not_started') echo 'selected'; ?>>Not Started</option>
            <option value="in_progress" <?php if (isset($_GET['filter']) && $_GET['filter'] === 'in_progress') echo 'selected'; ?>>In Progress</option>
            <option value="completed" <?php if (isset($_GET['filter']) && $_GET['filter'] === 'completed') echo 'selected'; ?>>Completed</option>
        </select>
    </form>

    </div>

    <table id="game-list">
        <tr>
            <th>Game Name</th>
            <th>Platform</th>
            <th>Date Created</th>
            <th>Date Started</th>
            <th>Date Completed</th>
        </tr>
        
        <?php foreach ($games as $game): ?>
        <tr>
            <td><?php echo htmlspecialchars($game['game_name']); ?></td>
            <td><?php echo htmlspecialchars($game['game_platform']); ?></td>
            <td><?php echo htmlspecialchars(substr($game['date_created'], 0, 10)); ?></td>
            <td><?php echo htmlspecialchars($game['date_started'] ? substr($game['date_started'], 0, 10) : 'not yet started'); ?></td>
            <td><?php echo htmlspecialchars($game['date_completed'] ? substr($game['date_completed'], 0, 10) : 'not yet completed'); ?></td>
            <!-- delete button -->
            <td>
                <div id = "action-buttons">
                <form method="post" action="delete_game.php" onsubmit="return confirm('Are you sure you want to delete this game?');">
                    <input type="hidden" name="game_name" value="<?php echo htmlspecialchars($game['game_name']); ?>">
                    <input type="hidden" name="game_platform" value="<?php echo htmlspecialchars($game['game_platform']); ?>">
                    <input type="submit" value="Delete">
                </form>
            
            <!-- Start button -->
            <?php if (empty($game['date_started'])): ?>
           
                <form method="post" action="start_game.php">
                    <input type="hidden" name="game_name" value="<?php echo htmlspecialchars($game['game_name']); ?>">
                    <input type="hidden" name="game_platform" value="<?php echo htmlspecialchars($game['game_platform']); ?>">
                    <input type="submit" value="Start">
                </form>

            <?php endif; ?>

            <!-- Complete Button -->
            <?php if (!empty($game['date_started']) && empty($game['date_completed'])): ?>
                <form method="post" action="complete_game.php">
                    <input type="hidden" name="game_name" value="<?php echo htmlspecialchars($game['game_name']); ?>">
                    <input type="hidden" name="game_platform" value="<?php echo htmlspecialchars($game['game_platform']); ?>">
                    <input type="submit" value="Complete">
                </form>
                </div>
            <?php endif; ?>

            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <div id="new-game">
    <h2>Add a New Game</h2>
    <form method="post" action="">
        <label for="game_name">Game Name:</label>
        <input type="text" id="game_name" name="game_name" required><br><br>
        <label for="game_platform">Platform:</label>
        <input type="text" id="game_platform" name="game_platform" required><br><br>
        <input type="submit" value="Add Game">
    </form>
    <a href="?action=logout">Logout</a>
    </div>
    
    
</body>
</html>
