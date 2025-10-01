<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Movies - AnimeHub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
   
    <?php include "header.php" ?>

    <main>

        <section class="movies-list">
            <div class="container">
                <div class="section-title">
                    <h3>Anime Movies</h3>
                </div>
                <div class="cards">
                <?php
                $sql = "SELECT * FROM movies ORDER BY id DESC LIMIT 3";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';

                    echo '<div class="card-content">';

                    echo '<div class="info-section">';
                    echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<div class="card-meta">';
                    echo '</div>';
                    echo '</div>';

                    echo '<a href="anime_info.php?id=' . $row['id'] . '" class="card-link"></a>';

                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            </div>
        </section>

        <section class="pagination">
            <div class="container">
                <div class="page-numbers">
                    <a href="#" class="active">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 AnimeHub. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="#">DMCA</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>
</body>
</html>