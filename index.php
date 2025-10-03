<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnimeHub - Watch Anime Online</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- NEW ATTRACTIVE CARD STYLES --- */

        /* 1. Making the cards smaller */
        .cards {
            /* This single line makes the cards smaller by setting a new minimum width */
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 25px; /* Increased the gap for a cleaner look */
        }

        /* 2. Improving the hover effect and general appearance */
        .movie-card {
            border-radius: 10px; /* Slightly more rounded corners */
            transition: all 0.3s ease-in-out; /* Smoother animation */
        }

        .movie-card:hover {
            transform: translateY(-5px) scale(1.05); /* Card lifts up and gets bigger */
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3); /* Adds a nice red glow */
        }
        
        /* 3. Enhancing the gradient for better text readability */
        .movie-card::after {
            background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.98) 100%);
        }

        /* 4. Adjusting the text to fit the new card size */
        .card-title {
            font-size: 1em; /* Made the title slightly smaller */
            margin-bottom: 8px; /* Added a bit of space below the title */
        }

        .card-meta {
            font-size: 0.8em; /* Adjusted metadata font size */
            gap: 12px;
            margin-top: 0;
            flex-wrap: wrap;
        }

        .card-meta span {
            gap: 4px;
            padding-right: 7px;
        }
    </style>
</head>

<body>

    <?php include "header.php"; ?>
    <?php include "slider.php" ?>

    <?php if (!isset($_SESSION['user'])): ?>
        <?php include "login.php" ?>
        <?php include "register.php" ?>
    <?php endif; ?>

    <main>
        <section class="section">
            <div class="container">
                <div>
                    <h3 class="section-title">Popular Titles</h3>
                </div>
                <div class="cards">
                    <?php
                    // This PHP logic is unchanged and works with the new styles
                    $sql = "
                        (SELECT 
                            m.id, m.title, m.image_path, 'movie' AS item_type, 
                            m.release_year, m.duration, NULL AS episode_count 
                        FROM movies m)
                        UNION ALL
                        (SELECT 
                            s.id, s.title, s.image_path, 'series' AS item_type, 
                            s.release_year, NULL AS duration, (SELECT COUNT(id) FROM episodes WHERE series_id = s.id) AS episode_count 
                        FROM series s)
                        ORDER BY id DESC
                        LIMIT 10
                    ";

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
                            if ($row['item_type'] === 'movie' && !empty($row['duration'])) {
                                echo '<span><i class="fas fa-clock"></i> ' . htmlspecialchars($row['duration']) . ' min</span>';
                            } elseif ($row['item_type'] === 'series') {
                                $ep_count = (int)$row['episode_count'];
                                $ep_text = ($ep_count > 0) ? $ep_count . ' Eps' : 'Upcoming';
                                echo '<span><i class="fas fa-tv"></i> ' . $ep_text . '</span>';
                            }
                            echo '</div>'; 
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

    <?php include "footer.php" ?>
</body>

</html>