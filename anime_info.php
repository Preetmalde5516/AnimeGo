<?php 
    // Include the header and database connection
    include "header.php";
    include "db_connect.php";

    // --- 1. GET ANIME ID & FETCH DATA ---
    // Check if the 'id' is set in the URL, otherwise redirect or show an error
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        // You can redirect to a 404 page or the home page
        header("Location: index.php");
        exit();
    }

    $anime_id = $_GET['id'];

    // Use PREPARED STATEMENTS to prevent SQL Injection
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $anime_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the anime data
    $anime = mysqli_fetch_assoc($result);

    // If no anime is found with that ID, handle the error
    if (!$anime) {
        echo "<div class='container' style='padding: 50px; text-align: center;'><h1>Anime Not Found</h1><p>Sorry, we couldn't find the anime you're looking for.</p></div>";
        include "footer.php";
        exit(); // Stop the script
    }

    // --- 2. PREPARE DYNAMIC DATA ---
    // Use htmlspecialchars to prevent XSS attacks when echoing data
    $title = htmlspecialchars($anime['title']);
    $japanese_title = htmlspecialchars($anime['japanese_title'] ?? 'N/A');
    $synopsis = htmlspecialchars($anime['description'] ?? 'No synopsis available.');
    $poster_image = htmlspecialchars($anime['image_path'] ?? 'default_poster.jpg');
    $background_image = htmlspecialchars($anime['background_image'] ?? 'default_background.jpg');
    $rating = htmlspecialchars($anime['rating'] ?? 'N/A');
    $quality = htmlspecialchars($anime['quality'] ?? 'HD');
    $type = htmlspecialchars($anime['type'] ?? 'TV');
    $duration = htmlspecialchars($anime['duration'] ?? 'N/A');
    $release_year = htmlspecialchars($anime['release_year'] ?? 'N/A');
    $studios = htmlspecialchars($anime['studios'] ?? 'N/A');
    
    // Handle genres - assuming they are stored as a comma-separated string like "Action, Adventure, Fantasy"
    $genres = !empty($anime['genres']) ? explode(', ', $anime['genres']) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS styles go here... */
        .anime-info-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                        url('assets/images/backgrounds/<?php echo $background_image; ?>') no-repeat center center/cover;
            min-height: 70vh;
            display: flex;
            align-items: center;
            position: relative;
        }

        .anime-info-content { display: grid; grid-template-columns: 250px 1fr 250px; gap: 30px; align-items: start; margin-top: 40px; }
        .anime-poster { position: sticky; top: 100px; }
        .anime-poster img { width: 100%; border-radius: 8px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5); }
        .breadcrumb { margin-bottom: 15px; font-size: 14px; color: #aaaaaa; }
        .breadcrumb a { color: #ff6b6b; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .anime-title { font-size: 2.5rem; font-weight: bold; margin-bottom: 20px; color: #ffffff; line-height: 1.2; }
        .anime-tags { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
        .tag { background-color: #ff6b6b; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .tag.rating { background-color: #4CAF50; }
        .tag.quality { background-color: #2196F3; }
        .tag.type { background-color: #FF9800; }
        .action-buttons { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn-watch, .btn-add { padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-watch { background-color: #ff6b6b; color: white; border: none; }
        .btn-watch:hover { background-color: #e55a5a; transform: translateY(-2px); }
        .btn-add { background-color: transparent; color: white; border: 2px solid #ff6b6b; }
        .btn-add:hover { background-color: #ff6b6b; color: white; }
        .synopsis-section { background-color: transparent; padding: 0; margin-bottom: 30px; }
        .synopsis-text { color: #cccccc; line-height: 1.6; margin-bottom: 20px; }
        .read-more { color: #ff6b6b; cursor: pointer; font-weight: 600; }
        .anime-metadata { background-color: transparent; padding: 0; position: sticky; top: 100px; }
        .metadata-item { margin-bottom: 15px; }
        .metadata-label { color: #ff6b6b; font-weight: 600; margin-right: 10px; }
        .metadata-value { color: #ffffff; }
        .genre-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px; }
        .genre-tag { background-color: #666666; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-decoration: none; transition: background-color 0.3s; font-weight: 500; }
        .genre-tag:hover { background-color: #888888; }
        .popular-section { margin-top: 50px; padding: 30px 0; }
        .popular-section h3, .section-title { color: #ff6b6b; margin-bottom: 20px; font-size: 1.5rem; }
        
        @media (max-width: 768px) {
            .anime-info-content { grid-template-columns: 1fr; }
            .anime-poster, .anime-metadata { position: static; text-align: center; }
            .anime-poster img { max-width: 250px; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    
    <main>
        <section class="anime-info-hero">
            <div class="container">
                <div class="anime-info-content">
                    <div class="anime-poster">
                        <img src="assets/images/<?php echo $poster_image; ?>" alt="<?php echo $title; ?>">
                    </div>

                    <div class="synopsis-section">
                        <div class="breadcrumb">
                            <a href="index.php">Home</a> > <a href="series.php?type=<?php echo $type; ?>"><?php echo $type; ?></a> > <?php echo $title; ?>
                        </div>
                        <h1 class="anime-title"><?php echo $title; ?></h1>
                        <div class="anime-tags">
                            <span class="tag rating"><?php echo $rating; ?></span>
                            <span class="tag quality"><?php echo $quality; ?></span>
                            <span class="tag type"><?php echo $type; ?></span>
                            <span class="tag"><?php echo $duration; ?></span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn-watch" data-anime-id="<?php echo $anime_id; ?>">
                                <i class="fas fa-play"></i> Watch now
                            </button>
                            <button class="btn-add" data-anime-id="<?php echo $anime_id; ?>">
                                <i class="fas fa-plus"></i> Add to List
                            </button>
                        </div>
                        <p class="synopsis-text"></p>
                        <div class="share-section">
                            <span>Share this anime with your friends!</span>
                        </div>
                    </div>

                    <div class="anime-metadata">
                        <div class="metadata-item">
                            <span class="metadata-label">Japanese:</span>
                            <span class="metadata-value"><?php echo $japanese_title; ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Title:</span>
                            <span class="metadata-value"><?php echo $title; ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Release Year:</span>
                            <span class="metadata-value"><?php echo $release_year; ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Duration:</span>
                            <span class="metadata-value"><?php echo $duration; ?></span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Genres:</span>
                            <div class="genre-tags">
                                <?php 
                                foreach ($genres as $genre) {
                                    echo '<a href="genre.php?name=' . urlencode(trim($genre)) . '" class="genre-tag">' . htmlspecialchars(trim($genre)) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Studios:</span>
                            <span class="metadata-value"><?php echo $studios; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const synopsisTextElement = document.querySelector('.synopsis-text');
        
        const fullText = <?php echo json_encode($synopsis ?? ''); ?>;
        
        const shortText = fullText.length > 300 ? fullText.substring(0, 300) + '...' : fullText;
        
        if (fullText.length > 300) {
            synopsisTextElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';
        } else {
            synopsisTextElement.innerHTML = fullText;
        }
        
        synopsisTextElement.addEventListener('click', function(e) {
            if (e.target.classList.contains('read-more')) {
                if (e.target.textContent === '+ More') {
                    synopsisTextElement.innerHTML = fullText + ' <span class="read-more">- Less</span>';
                } else {
                    synopsisTextElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';
                }
            }
        });

        const addToListBtn = document.querySelector('.btn-add');
        addToListBtn.addEventListener('click', function() {
            console.log('Adding anime ID ' + this.dataset.animeId + ' to list.');
            
            this.innerHTML = '<i class="fas fa-check"></i> Added to List';
            this.style.backgroundColor = '#4CAF50';
            this.style.borderColor = '#4CAF50';
            this.disabled = true;
        });

        const watchBtn = document.querySelector('.btn-watch');
        watchBtn.addEventListener('click', function() {
            // **FIXED**: Changed 'id' to 'movie_id' to match watchpage.php
            window.location.href = 'watchpage.php?movie_id=' + this.dataset.animeId;
        });
    });
    
    </script>
</body>
</html>