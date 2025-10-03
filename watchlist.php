<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch watchlist items for the current user
$watchlist_sql = "
    SELECT 
        w.content_id, 
        w.content_type,
        CASE
            WHEN w.content_type = 'movie' THEN m.title
            WHEN w.content_type = 'series' THEN s.title
        END AS title,
        CASE
            WHEN w.content_type = 'movie' THEN m.image_path
            WHEN w.content_type = 'series' THEN s.image_path
        END AS image_path
    FROM user_watchlist w
    LEFT JOIN movies m ON w.content_id = m.id AND w.content_type = 'movie'
    LEFT JOIN series s ON w.content_id = s.id AND w.content_type = 'series'
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
";

$stmt = $conn->prepare($watchlist_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$watchlist_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .watchlist-item {
            position: relative;
        }
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 107, 107, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .remove-btn:hover {
            background-color: #e55a5a;
        }
    </style>
</head>
<body>

    <?php include "header.php"; ?>

    <main>
        <section class="section">
            <div class="container">
                <h3 class="section-title">My Watchlist</h3>
                <div class="cards">
                    <?php if ($watchlist_result && $watchlist_result->num_rows > 0): ?>
                        <?php while ($item = $watchlist_result->fetch_assoc()): ?>
                            <div class="watchlist-item">
                                <form action="add_to_watchlist.php" method="POST">
                                    <input type="hidden" name="content_id" value="<?php echo $item['content_id']; ?>">
                                    <input type="hidden" name="content_type" value="<?php echo $item['content_type']; ?>">
                                    <button type="submit" class="remove-btn" title="Remove from Watchlist">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <div class="movie-card" style="background-image: url('assets/images/<?php echo htmlspecialchars($item['image_path']); ?>');">
                                    <div class="card-content">
                                        <h3 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    </div>
                                    <a href="anime_info.php?id=<?php echo $item['content_id']; ?>&type=<?php echo $item['content_type']; ?>" class="card-link"></a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Your watchlist is empty. Browse movies and series to add them!</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>

</body>
</html>