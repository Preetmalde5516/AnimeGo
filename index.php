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
            
            $sql = "(SELECT id, title, image_path, 'movie' AS item_type FROM movies)
                    UNION ALL
                    (SELECT id, title, image_path, 'series' AS item_type FROM series)
                    ORDER BY id DESC
                    LIMIT 10";

            $result = mysqli_query($conn, $sql);

            // Check if the query was successful and returned results
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Main card container with background image
                    echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                    echo '<div class="card-content">';
                    echo '<div class="info-section">';
                    echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '</div>'; 
                    echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';

                    echo '</div>'; // end .card-content
                    echo '</div>'; // end .movie-card
                }
            } else {
                // Display a message if no movies or series are found
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