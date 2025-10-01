<?php
// Include the header and database connection
include "header.php";
include "db_connect.php"; // Make sure this file has the $conn variable

// --- 1. GET ITEM ID & TYPE, THEN VALIDATE ---
// Check if both 'id' and 'type' are set in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type'])) {
    header("Location: index.php"); // Redirect to home if info is missing
    exit();
}

$item_id = (int)$_GET['id'];
$item_type = $_GET['type'];

// **KEY CHANGE**: Determine the correct table name based on the 'type' parameter.
// This is a security measure to prevent arbitrary table names in the query.
$tableName = '';
if ($item_type === 'movie') {
    $tableName = 'movies';
} elseif ($item_type === 'series') {
    $tableName = 'series';
} else {
    // If the type is not 'movie' or 'series', stop the script.
    echo "<div class='container' style='padding: 50px; text-align: center;'><h1>Invalid Type</h1><p>The content type specified is not valid.</p></div>";
    include "footer.php";
    exit();
}

// --- 2. FETCH DATA FROM THE CORRECT TABLE ---
// Use PREPARED STATEMENTS with the dynamic table name
$sql = "SELECT * FROM {$tableName} WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

// If no item is found with that ID in the specified table, handle the error
if (!$item) {
    echo "<div class='container' style='padding: 50px; text-align: center;'><h1>Content Not Found</h1><p>Sorry, we couldn't find what you're looking for.</p></div>";
    include "footer.php";
    exit(); // Stop the script
}

// --- 3. PREPARE DYNAMIC DATA FOR DISPLAY ---
// Use htmlspecialchars to prevent XSS attacks when echoing data
$title = htmlspecialchars($item['title']);
$japanese_title = htmlspecialchars($item['japanese_title'] ?? 'N/A');
$synopsis = htmlspecialchars($item['description'] ?? 'No synopsis available.');
$poster_image = htmlspecialchars($item['image_path'] ?? 'default_poster.jpg');
$background_image = htmlspecialchars($item['background_image'] ?? 'default_background.jpg');
$rating = htmlspecialchars($item['rating'] ?? 'N/A');
$quality = htmlspecialchars($item['quality'] ?? 'HD');
// Note: $type variable is from the database, while $item_type is from the URL. They should be the same.
$type = htmlspecialchars($item['type'] ?? 'TV');
$duration = htmlspecialchars($item['duration'] ?? 'N/A');
$release_year = htmlspecialchars($item['release_year'] ?? 'N/A');
$studios = htmlspecialchars($item['studios'] ?? 'N/A');
$genres = !empty($item['genres']) ? explode(', ', $item['genres']) : [];
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
        /* Your existing CSS styles... (No changes needed here) */
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
        .synopsis-text { color: #cccccc; line-height: 1.6; margin-bottom: 20px; }
        .read-more { color: #ff6b6b; cursor: pointer; font-weight: 600; }
        .anime-metadata { position: sticky; top: 100px; }
        .metadata-item { margin-bottom: 15px; }
        .metadata-label { color: #ff6b6b; font-weight: 600; margin-right: 10px; }
        .metadata-value { color: #ffffff; }
        .genre-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px; }
        .genre-tag { background-color: #666666; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-decoration: none; }
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
                            <a href="index.php">Home</a> > <a href="category.php?type=<?php echo urlencode($item_type); ?>"><?php echo ucfirst($item_type); ?></a> > <?php echo $title; ?>
                        </div>
                        <h1 class="anime-title"><?php echo $title; ?></h1>
                        <div class="anime-tags">
                            <span class="tag rating"><?php echo $rating; ?></span>
                            <span class="tag quality"><?php echo $quality; ?></span>
                            <span class="tag type"><?php echo $type; ?></span>
                            <span class="tag"><?php echo $duration; ?></span>
                        </div>
                        <div class="action-buttons">
                            <!-- **KEY CHANGE**: Added data-item-type to pass the type to JS -->
                            <button class="btn-watch" data-item-id="<?php echo $item_id; ?>" data-item-type="<?php echo $item_type; ?>">
                                <i class="fas fa-play"></i> Watch now
                            </button>
                            <button class="btn-add" data-item-id="<?php echo $item_id; ?>">
                                <i class="fas fa-plus"></i> Add to List
                            </button>
                        </div>
                        <p class="synopsis-text"></p>
                    </div>

                    <div class="anime-metadata">
                        <!-- Metadata section remains the same, displaying fetched data -->
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
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div>
                    <!-- **KEY CHANGE**: Changed title to be more generic -->
                    <h3 class="section-title">You Might Also Like</h3>
                </div>
                <div class="cards">
                    <?php
                    // **KEY CHANGE**: Updated query to fetch a mix of popular movies AND series
                    $popular_sql = "(SELECT id, title, image_path, 'movie' as item_type FROM movies)
                                    UNION ALL
                                    (SELECT id, title, image_path, 'series' as item_type FROM series)
                                    ORDER BY RAND()
                                    LIMIT 5";
                    $popular_result = mysqli_query($conn, $popular_sql);
                    while ($row = mysqli_fetch_assoc($popular_result)) {
                        echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                        echo '<div class="card-content">';
                        echo '<div class="info-section">';
                        echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                        echo '</div>';
                        // **KEY CHANGE**: The link now correctly includes the type
                        echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';
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
            console.log('Adding item ID ' + this.dataset.itemId + ' to list.');
            this.innerHTML = '<i class="fas fa-check"></i> Added';
            this.style.backgroundColor = '#4CAF50';
            this.style.borderColor = '#4CAF50';
            this.disabled = true;
        });

        const watchBtn = document.querySelector('.btn-watch');
        watchBtn.addEventListener('click', function() {
            // **KEY CHANGE**: Get both id and type to build the correct URL
            const itemId = this.dataset.itemId;
            const itemType = this.dataset.itemType;
            // Redirect to a watch page that can also handle 'type'
            window.location.href = `watchpage.php?id=${itemId}&type=${itemType}`;
        });
    });
    </script>
</body>
</html>