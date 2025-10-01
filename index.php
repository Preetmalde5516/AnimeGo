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
            <h3 class="section-title">Popular Animes</h3>
            </div>
            <div class="cards">
                <?php
                $sql = "SELECT * FROM movies ORDER BY id DESC LIMIT 5";
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
        
    </main>


        <?php include "footer.php" ?>
</body>

</html>