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
    <title>Anime Movies - AnimeHub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- NEW ATTRACTIVE CARD STYLES --- */
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
        }
        .card-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
   
    <?php include "header.php" ?>

    <main>
        <section class="movies-list">
            <div class="container">
                <div class="section-title">
                    <h3>All Movies</h3>
                </div>
                <div class="cards">
                <?php
                // --- UPDATED SQL QUERY ---
                $sql = "SELECT id, title, image_path, release_year, duration FROM movies ORDER BY id DESC";
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
                        if (!empty($row['duration'])) {
                            echo '<span><i class="fas fa-clock"></i> ' . htmlspecialchars($row['duration']) . ' min</span>';
                        }
                        echo '</div>'; // end .card-meta

                        echo '</div>'; // end .info-section
                        echo '<a href="anime_info.php?id=' . $row['id'] . '&type=movie" class="card-link"></a>';
                        echo '</div>'; // end .card-content
                        echo '</div>'; // end .movie-card
                    }
                } else {
                    echo "<p>No movies found at the moment.</p>";
                }
                ?>

            </div>
            </div>
        </section>
    </main> 

    <?php include "footer.php"; ?>
</body>
</html>