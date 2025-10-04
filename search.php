<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php'; // Include the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reusing the card styles from your index page for consistency */
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
        .card-title { font-size: 1em; margin-bottom: 8px; }
        .card-meta { font-size: 0.8em; color: #ccc; display: flex; gap: 12px; align-items: center; }
        .card-meta span { display: flex; align-items: center; gap: 4px; }
    </style>
</head>
<body>

    <?php include "header.php"; ?>

    <main>
        <section class="section">
            <div class="container">
                <?php
                // Get the search query from the URL
                $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
                ?>
                <h3 class="section-title">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                
                <div class="cards">
                    <?php
                    if (!empty($search_query)) {
                        // Prepare the search term for a LIKE query
                        $search_term = "%" . $search_query . "%";

                        // SQL query to search in both movies and series tables
                        $sql = "
                            (SELECT 
                                m.id, m.title, m.image_path, 'movie' AS item_type, 
                                m.release_year, m.duration, NULL AS episode_count 
                            FROM movies m WHERE m.title LIKE ?)
                            UNION ALL
                            (SELECT 
                                s.id, s.title, s.image_path, 'series' AS item_type, 
                                s.release_year, NULL AS duration, (SELECT COUNT(id) FROM episodes WHERE series_id = s.id) AS episode_count 
                            FROM series s WHERE s.title LIKE ?)
                            ORDER BY title ASC
                        ";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ss", $search_term, $search_term);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Display each result using the existing card layout
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
                                echo '</div>'; // end .card-meta
                                echo '</div>'; // end .info-section
                                echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';
                                echo '</div>'; // end .card-content
                                echo '</div>'; // end .movie-card
                            }
                        } else {
                            echo "<p>No results found for your search.</p>";
                        }
                        $stmt->close();
                    } else {
                        echo "<p>Please enter a search term in the search bar above.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>

</body>
</html>
