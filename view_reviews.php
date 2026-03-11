<?php
session_start();

if (!isset($_SESSION['user_id'])) {
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

// Fetch all reviews with user names
$statement = $pdo->prepare("
    SELECT r.review_id, r.game_name, r.game_platform, r.content, r.stars, r.likes, r.dislikes, r.date_submitted, u.name AS user_name
    FROM review r
    JOIN user u ON r.user_id = u.user_id
    ORDER BY r.date_submitted DESC
");
$statement->execute();
$reviews = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id = "review-page">
    <div id = "review-content">
    <h1>All Game Reviews</h1>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Game Name</th>
                <th>Platform</th>
                <th>User</th>
                <th>Date Submitted</th>
                <th>Rating</th>
                <th>Review Content</th>
                <th>Likes</th>
                <th>Dislikes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['game_name']); ?></td>
                        <td><?php echo htmlspecialchars($review['game_platform']); ?></td>
                        <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($review['date_submitted'], 0, 10)); ?></td>
                        <td><?php echo htmlspecialchars($review['stars']); ?> ⭐</td>
                        <td><?php echo htmlspecialchars($review['content']); ?></td>
                        <td><?php echo htmlspecialchars($review['likes']); ?></td>
                        <td><?php echo htmlspecialchars($review['dislikes']); ?></td>
                        <td>
                            <form method="post" action="like_review.php" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                <input type="submit" value="Like">
                            </form>
                            <form method="post" action="dislike_review.php" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                <input type="submit" value="Dislike">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No reviews found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    </div>
    <a href="backlog.php">Back to Backlog</a>
</body>
</html>
