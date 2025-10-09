<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnimeHub - Watch Anime Online</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include "header.php"; ?>
    <?php include "slider.php"; ?>

    <main>
        <section class="section">
            <div class="container">
                <h2 class="section-title">Popular Titles</h2>
                <div class="cards">
                    <?php
                    $sql = "(SELECT id, title, image_path, 'movie' AS item_type, release_year, duration, NULL AS episode_count FROM movies)
                            UNION ALL
                            (SELECT s.id, s.title, s.image_path, 'series' AS item_type, s.release_year, NULL AS duration, (SELECT COUNT(id) FROM episodes WHERE series_id = s.id) AS episode_count FROM series s)
                            ORDER BY id DESC
                            LIMIT 10";

                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                            echo '<div class="card-content">';
                            echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                            echo '<div class="card-meta">';
                            if (!empty($row['release_year'])) {
                                echo '<span><i class="fas fa-calendar-alt"></i> ' . htmlspecialchars($row['release_year']) . '</span>';
                            }
                            if ($row['item_type'] === 'movie' && !empty($row['duration'])) {
                                echo '<span><i class="fas fa-clock"></i> ' . htmlspecialchars($row['duration']) . ' min</span>';
                            } elseif ($row['item_type'] === 'series') {
                                $ep_count = (int)$row['episode_count'];
                                $ep_text = ($ep_count > 0) ? $ep_count . ' Eps' : 'Upcoming';
                                echo '<span><i class="fas fa-tv"></i> ' . $ep_text . '</span>';
                            }
                            echo '</div>'; 
                            echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';
                            echo '</div>'; 
                            echo '</div>'; 
                        }
                    } else {
                        echo "<p>No popular titles found at the moment.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>