<?php
// Include the header and database connection
include "../includes/header.php";

// --- 1. GET ITEM ID & TYPE, THEN VALIDATE ---
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type'])) {
    header("Location: index.php"); // Redirect to home if info is missing
    exit();
}

$item_id = (int) $_GET['id'];
$item_type = $_GET['type'];
$item = null;

// --- 2. FETCH DATA FROM THE CORRECT TABLE (Corrected Logic) ---
if ($item_type === 'movie') {
    $sql = "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            WHERE m.id = ?
            GROUP BY m.id";
} elseif ($item_type === 'series') {
    $sql = "SELECT s.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
            FROM series s
            LEFT JOIN series_genres sg ON s.id = sg.series_id
            LEFT JOIN genres g ON sg.genre_id = g.id
            WHERE s.id = ?
            GROUP BY s.id";
} else {
    // If the type is not 'movie' or 'series', stop the script.
    echo "<div class='container' style='padding: 50px; text-align: center;'><h1>Invalid Type</h1><p>The content type specified is not valid.</p></div>";
    include "../includes/footer.php";
    exit();
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);

// If no item is found, handle the error
if (!$item) {
    echo "<div class='container' style='padding: 50px; text-align: center;'><h1>Content Not Found</h1><p>Sorry, we couldn't find what you're looking for.</p></div>";
    include "../includes/footer.php";
    exit(); // Stop the script
}

// --- Check if the item is in the user's watchlist ---
$in_watchlist = false;
if (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
    $check_stmt = $conn->prepare("SELECT id FROM user_watchlist WHERE user_id = ? AND content_id = ? AND content_type = ?");
    $check_stmt->bind_param("iis", $user_id, $item_id, $item_type);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $in_watchlist = true;
    }
    $check_stmt->close();
}


// --- 3. PREPARE DYNAMIC DATA FOR DISPLAY ---
// Use htmlspecialchars to prevent XSS attacks when echoing data
$title = htmlspecialchars($item['title']);
$japanese_title = htmlspecialchars($item['Japnese'] ?? 'N/A');
$synopsis = htmlspecialchars($item['description'] ?? 'No synopsis available.');
$poster_image = htmlspecialchars($item['image_path'] ?? 'default_poster.jpg');
$background_image = htmlspecialchars($item['thumbnail_path'] ?? $item['image_path'] ?? 'default_background.jpg');
$rating = 'N/A'; // Default value
$quality = 'HD'; // Default value
$type = ($item_type === 'movie') ? 'Movie' : 'TV'; // Determine type from URL parameter
$duration = isset($item['duration']) ? htmlspecialchars($item['duration']) . ' min' : 'N/A';
$release_year = htmlspecialchars($item['release_year'] ?? 'N/A');
$studios = 'N/A'; // Default value
$genres = !empty($item['genres']) ? explode(', ', $item['genres']) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - AnimeGo</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <main>
        <section class="anime-info-hero"
            style="background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('../assets/thumbnail/<?php echo $background_image; ?>');">
            <div class="container">
                <div class="anime-info-content">
                    <div class="anime-poster">
                        <img src="../assets/images/<?php echo $poster_image; ?>" alt="<?php echo $title; ?>">
                    </div>

                    <div class="synopsis-section">
                        <div class="breadcrumb">
                            <a href="index.php">Home</a> > <a
                                href="category.php?type=<?php echo urlencode($item_type); ?>"><?php echo ucfirst($item_type); ?></a>
                            > <?php echo $title; ?>
                        </div>
                        <h1 class="anime-title"><?php echo $title; ?></h1>
                        <div class="anime-tags">
                            <span class="tag rating"><?php echo $rating; ?></span>
                            <span class="tag quality"><?php echo $quality; ?></span>
                            <span class="tag type"><?php echo $type; ?></span>
                            <span class="tag"><?php echo $duration; ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="watchpage.php?id=<?php echo $item_id; ?>&type=<?php echo $item_type; ?><?php if ($item_type === 'series')
                                      echo '&ep=1'; ?>" class="btn-watch">
                                <i class="fas fa-play"></i> Watch now
                            </a>
                            <form action="../utils/add_to_watchlist.php" method="POST" style="margin:0;">
                                <input type="hidden" name="content_id" value="<?php echo $item_id; ?>">
                                <input type="hidden" name="content_type" value="<?php echo $item_type; ?>">
                                <?php if ($in_watchlist): ?>
                                    <button type="submit" class="btn-add"
                                        style="background-color: #4CAF50; border-color: #4CAF50;">
                                        <i class="fas fa-check"></i> Added
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn-add">
                                        <i class="fas fa-plus"></i> Add to List
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                        <p class="synopsis-text"></p>
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
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div>
                    <h3 class="section-title">You Might Also Like</h3>
                </div>
                <div class="cards">
                    <?php
                    // Updated query to fetch a mix of popular movies AND series
                    $popular_sql = "(SELECT id, title, image_path, 'movie' as item_type FROM movies WHERE id != ?)
                                    UNION ALL
                                    (SELECT id, title, image_path, 'series' as item_type FROM series WHERE id != ?)
                                    ORDER BY RAND()
                                    LIMIT 5";
                    $popular_stmt = mysqli_prepare($conn, $popular_sql);
                    mysqli_stmt_bind_param($popular_stmt, "ii", $item_id, $item_id);
                    mysqli_stmt_execute($popular_stmt);
                    $popular_result = mysqli_stmt_get_result($popular_stmt);
                    while ($row = mysqli_fetch_assoc($popular_result)) {
                        echo '<div class="movie-card" style="background-image: url(\'../assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                        echo '<div class="card-content">';
                        echo '<div class="info-section">';
                        echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                        echo '</div>';
                        // The link now correctly includes the type
                        echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>

    <script>
        // This script is only for the "Read More" synopsis functionality
        document.addEventListener('DOMContentLoaded', function () {
            const synopsisTextElement = document.querySelector('.synopsis-text');
            const fullText = <?php echo json_encode($synopsis ?? ''); ?>;
            const shortText = fullText.length > 300 ? fullText.substring(0, 300) + '...' : fullText;

            if (fullText.length > 300) {
                synopsisTextElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';
            } else {
                synopsisTextElement.innerHTML = fullText;
            }

            synopsisTextElement.addEventListener('click', function (e) {
                if (e.target.classList.contains('read-more')) {
                    if (e.target.textContent === '+ More') {
                        synopsisTextElement.innerHTML = fullText + ' <span class="read-more">- Less</span>';
                    } else {
                        synopsisTextElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';
                    }
                }
            });
        });
    </script>
</body>

</html>