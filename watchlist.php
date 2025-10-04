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

// --- UPDATED SQL QUERY ---
// This query now fetches all the necessary details for the new card style.
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
        END AS image_path,
        CASE
            WHEN w.content_type = 'movie' THEN m.release_year
            WHEN w.content_type = 'series' THEN s.release_year
        END AS release_year,
        m.duration,
        (SELECT COUNT(id) FROM episodes WHERE series_id = s.id) AS episode_count
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
        /* --- NEW ATTRACTIVE CARD STYLES (from index.php) --- */
        .cards {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 25px;
        }
        .movie-card {
            border-radius: 10px;
            transition: all 0.3s ease-in-out;
        }
        .movie-card:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
        }
        .movie-card::after {
            background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.98) 100%);
        }
        .card-title {
            font-size: 1em;
            margin-bottom: 8px;
        }
        .card-meta {
            font-size: 0.8em;
            color: #ccc;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .card-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Styles for the remove button container */
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
            transition: background-color 0.2s;
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
                                        <div class="info-section">
                                            <h3 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                            <div class="card-meta">
                                                <?php if (!empty($item['release_year'])): ?>
                                                    <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($item['release_year']); ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['content_type'] === 'movie' && !empty($item['duration'])): ?>
                                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($item['duration']); ?> min</span>
                                                <?php elseif ($item['content_type'] === 'series'): ?>
                                                    <?php
                                                        $ep_count = (int)$item['episode_count'];
                                                        $ep_text = ($ep_count > 0) ? $ep_count . ' Eps' : 'Upcoming';
                                                    ?>
                                                    <span><i class="fas fa-tv"></i> <?php echo $ep_text; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="anime_info.php?id=<?php echo $item['content_id']; ?>&type=<?php echo $item['content_type']; ?>" class="card-link"></a>
                                    </div>
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