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
    <title>Anime Series - AnimeHub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include "header.php" ?>
    <main>
        <section class="series-list">
            <div class="container">
                <div>
                <h3>All Series</h3>
                </div>
                <div class="cards">
                <?php
                // --- UPDATED SQL QUERY ---
                $sql = "SELECT s.id, s.title, s.image_path, s.release_year, 
                               (SELECT COUNT(id) FROM episodes WHERE series_id = s.id) AS episode_count 
                        FROM series s 
                        ORDER BY s.id DESC";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                        echo '<div class="card-content">';
                        echo '<div class="info-section">';
                        
                        echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                        
                        echo '<div class="card-meta">';
                        if (!empty($row['release_year'])) {
                            echo '<span><i class="fas fa-calendar-alt"></i> ' . htmlspecialchars($row['release_year']) . '</span>';
                        }
                        $ep_count = (int)$row['episode_count'];
                        $ep_text = ($ep_count > 0) ? $ep_count . ' Eps' : 'Upcoming';
                        echo '<span><i class="fas fa-tv"></i> ' . $ep_text . '</span>';
                        echo '</div>'; // end .card-meta

                        echo '</div>'; // end .info-section
                        echo '<a href="anime_info.php?id=' . $row['id'] . '&type=series" class="card-link"></a>';
                        echo '</div>'; // end .card-content
                        echo '</div>'; // end .movie-card
                    }
                } else {
                    echo "<p>No series found at the moment.</p>";
                }
                ?>
            </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>