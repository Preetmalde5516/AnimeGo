<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php'; // Include your database connection

// --- 1. VALIDATE URL PARAMETERS ---
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type'])) {
    // Redirect or show error if parameters are missing
    header("Location: index.php");
    exit();
}

$item_id = (int)$_GET['id'];
$item_type = $_GET['type'];
$item = null;
$episodes = [];
$current_episode_number = 1; // Default for series

// --- 2. FETCH DATA BASED ON TYPE (MOVIE OR SERIES) ---
$tableName = '';
if ($item_type === 'movie') {
    $tableName = 'movies';
} elseif ($item_type === 'series') {
    $tableName = 'series';
} else {
    die("Invalid content type.");
}

// Fetch the main item details (movie or series)
$stmt = $conn->prepare("SELECT * FROM {$tableName} WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
} else {
    die("Content not found.");
}
$stmt->close();

// If it's a series, fetch all its episodes
if ($item_type === 'series') {
    $stmt = $conn->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY episode_number ASC");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $episodes[] = $row;
    }
    $stmt->close();
    
    // Determine which episode to play
    if (isset($_GET['ep']) && is_numeric($_GET['ep'])) {
        $current_episode_number = (int)$_GET['ep'];
    }
}

// --- 3. PREPARE VARIABLES FOR DISPLAY ---
$title = htmlspecialchars($item['title']);
$synopsis = htmlspecialchars($item['description'] ?? 'No synopsis available.');
$poster_image = htmlspecialchars($item['image_path'] ?? 'default_poster.jpg');
// These fields might not exist in the series table, so we use the null coalescing operator
$rating = htmlspecialchars($item['rating'] ?? 'N/A'); 
$quality = htmlspecialchars($item['quality'] ?? 'HD');
$duration = htmlspecialchars($item['duration'] ?? 'N/A');

// Determine the video source
$video_src = '';
if ($item_type === 'movie') {
    $video_src = $item['video_path'] ?? '';
} elseif (!empty($episodes)) {
    // Find the video path for the current episode
    foreach ($episodes as $episode) {
        if ($episode['episode_number'] == $current_episode_number) {
            $video_src = $episode['video_path'] ?? '';
            break;
        }
    }
    // Fallback to the first episode if the requested one doesn't exist
    if(empty($video_src)) {
        $video_src = $episodes[0]['video_path'] ?? '';
        $current_episode_number = $episodes[0]['episode_number'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch <?php echo $title; ?> - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS from the static file... */
        body { background-color: #121212; color: #fff; margin:0; }
        .watch-container { display: flex; flex-direction: column; min-height: 100vh; }
        .watch-content { display: grid; grid-template-columns: 250px 1fr 300px; flex: 1; gap: 0; }
        .episodes-sidebar { background-color: #1a1a1a; padding: 20px; border-right: 1px solid #333; overflow-y: auto; max-height: calc(100vh - 80px); }
        .episodes-title { color: #ff6b6b; font-size: 18px; margin-bottom: 15px; font-weight: 600; }
        .episode-list { list-style: none; padding: 0; margin: 0; }
        .episode-item { background-color: #2a2a2a; margin-bottom: 8px; border-radius: 6px; transition: all 0.3s; cursor: pointer; }
        .episode-item:hover { background-color: #333; }
        .episode-item.active { background: #ff6b6b; border: 1px solid #fff; }
        .episode-link { display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; color: white; text-decoration: none; }
        .episode-number { font-weight: 600; margin-bottom: 2px; }
        .play-icon { color: #4CAF50; font-size: 16px; }
        .video-container { background-color: #000; display: flex; flex-direction: column; position: relative; }
        .video-player { width: 100%; height: 80vh; background-color: #000; position: relative; }
        #main-video { width: 100%; height: 100%; }
        .video-controls { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 10px; display: flex; align-items: center; justify-content: space-between; opacity: 0; transition: opacity 0.3s; }
        .video-player:hover .video-controls { opacity: 1; }
        .control-left, .control-right { display: flex; align-items: center; gap: 15px; }
        .control-btn, .play-pause-btn { background: none; border: none; color: white; font-size: 18px; cursor: pointer; }
        .play-pause-btn { font-size: 24px; }
        .time-display { font-size: 14px; }
        .current-episode { padding: 15px; background-color: #1a1a1a; text-align: center; font-weight: 600; }
        .anime-info-sidebar { background-color: #1a1a1a; padding: 20px; border-left: 1px solid #333; overflow-y: auto; max-height: calc(100vh - 80px); }
        .anime-poster { width: 100%; border-radius: 8px; margin-bottom: 15px; }
        .anime-title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .synopsis { font-size: 14px; line-height: 1.5; margin-bottom: 15px; color: #ccc; }
        .read-more { color: #ff6b6b; cursor: pointer; font-size: 12px; }
        .view-detail-btn { background-color: #ff6b6b; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; width: 100%; margin-bottom: 15px; text-align: center; text-decoration: none; display: block; }
        .section { padding: 40px 0; }
        /* Responsive styles */
        @media (max-width: 1024px) { .watch-content { grid-template-columns: 1fr; grid-template-rows: auto auto auto; } .episodes-sidebar, .anime-info-sidebar { max-height: 300px; order: 3; border:0; border-top: 1px solid #333; } .video-container { order: 1; } .anime-info-sidebar { order: 2; } .video-player { height: 56.25vw; /* 16:9 aspect ratio */ } }

    </style>
</head>
<body>
    <div class="watch-container">
        <?php include 'header.php'; ?>

        <div class="watch-content">
            <!-- Left Sidebar - Episodes (Only shows for series) -->
            <aside class="episodes-sidebar">
                <h3 class="episodes-title">List of episodes:</h3>
                <?php if ($item_type === 'series' && !empty($episodes)): ?>
                    <ul class="episode-list">
                        <?php foreach ($episodes as $ep): ?>
                            <li class="episode-item <?php echo ($ep['episode_number'] == $current_episode_number) ? 'active' : ''; ?>" 
                                data-ep-number="<?php echo $ep['episode_number']; ?>" 
                                data-video-src="assets/videos/<?php echo htmlspecialchars($ep['video_path']); ?>">
                                <a href="watchpage.php?id=<?php echo $item_id; ?>&type=series&ep=<?php echo $ep['episode_number']; ?>" class="episode-link">
                                    <div class="episode-info">
                                        <div class="episode-number">Episode <?php echo $ep['episode_number']; ?></div>
                                    </div>
                                    <i class="fas fa-play play-icon"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>This is a movie and does not have an episode list.</p>
                <?php endif; ?>
            </aside>

            <!-- Center - Video Player -->
            <main class="video-container">
                <div class="video-player">
                    <video id="main-video" src="assets/videos/<?php echo htmlspecialchars($video_src); ?>" controls></video>
                </div>
                <div class="current-episode">
                    You are watching: <strong><?php echo $title; ?></strong>
                    <?php if ($item_type === 'series'): ?>
                        - Episode <?php echo $current_episode_number; ?>
                    <?php endif; ?>
                </div>
            </main>

            <!-- Right Sidebar - Anime Info -->
            <aside class="anime-info-sidebar">
                <img src="assets/images/<?php echo $poster_image; ?>" alt="Poster for <?php echo $title; ?>" class="anime-poster">
                <h2 class="anime-title"><?php echo $title; ?></h2>
                <div class="synopsis"><?php echo $synopsis; ?></div>
                <a href="anime_info.php?id=<?php echo $item_id; ?>&type=<?php echo $item_type; ?>" class="view-detail-btn">View Full Details</a>
            </aside>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <h3 class="section-title">You Might Also Like</h3>
            <div class="cards">
                 <?php
                    $popular_sql = "(SELECT id, title, image_path, 'movie' as item_type FROM movies)
                                    UNION ALL
                                    (SELECT id, title, image_path, 'series' as item_type FROM series)
                                    ORDER BY RAND() LIMIT 3";
                    $popular_result = mysqli_query($conn, $popular_sql);
                    while ($row = mysqli_fetch_assoc($popular_result)) {
                        echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                        echo '<div class="card-content">';
                        echo '<div class="info-section"><h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3></div>';
                        echo '<a href="anime_info.php?id=' . $row['id'] . '&type=' . $row['item_type'] . '" class="card-link"></a>';
                        echo '</div></div>';
                    }
                    mysqli_close($conn);
                ?>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const videoPlayer = document.getElementById('main-video');
        const episodeItems = document.querySelectorAll('.episode-item');

        // This function handles clicks on episode list items
        episodeItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // We use a link now, so JS interaction can be simpler or removed
                // e.preventDefault(); 
                // const videoSrc = this.dataset.videoSrc;
                // if (videoSrc && videoSrc !== '#') {
                //     videoPlayer.src = videoSrc;
                //     videoPlayer.play();
                //     document.querySelector('.current-episode strong').textContent = '<?php echo $title; ?> - Episode ' + this.dataset.epNumber;
                //     episodeItems.forEach(ep => ep.classList.remove('active'));
                //     this.classList.add('active');
                // }
            });
        });

        // Synopsis "Read more" functionality
        const synopsisElement = document.querySelector('.synopsis');
        if (synopsisElement) {
            const fullText = <?php echo json_encode($synopsis); ?>;
            if (fullText.length > 150) {
                const shortText = fullText.substring(0, 150) + '...';
                synopsisElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';

                synopsisElement.addEventListener('click', function(e) {
                    if (e.target.classList.contains('read-more')) {
                        if (e.target.textContent === '+ More') {
                            synopsisElement.innerHTML = fullText + ' <span class="read-more">- Less</span>';
                        } else {
                            synopsisElement.innerHTML = shortText + ' <span class="read-more">+ More</span>';
                        }
                    }
                });
            }
        }
    });
    </script>
</body>
</html>
