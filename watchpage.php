<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php'; // Your database connection file

// --- BACKEND LOGIC ---

// 1. Get Series ID and Episode Number from URL
$series_id = isset($_GET['series_id']) ? (int)$_GET['series_id'] : 1; // Default to series 1 if not set
$current_episode_number = isset($_GET['episode_number']) ? (int)$_GET['episode_number'] : 1; // Default to episode 1

// 2. Fetch Series Details
$stmt = $conn->prepare("SELECT * FROM series WHERE id = ?");
$stmt->bind_param("i", $series_id);
$stmt->execute();
$series_result = $stmt->get_result();
$series = $series_result->fetch_assoc();

// 3. Fetch Series Genres
$genre_stmt = $conn->prepare("
    SELECT g.name FROM genres g
    JOIN series_genres sg ON g.id = sg.genre_id
    WHERE sg.series_id = ?
");
$genre_stmt->bind_param("i", $series_id);
$genre_stmt->execute();
$genres_result = $genre_stmt->get_result();
$genres = [];
while ($row = $genres_result->fetch_assoc()) {
    $genres[] = $row;
}


// 4. Fetch the full list of episodes for the sidebar
$episodes_stmt = $conn->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY episode_number ASC");
$episodes_stmt->bind_param("i", $series_id);
$episodes_stmt->execute();
$episodes_list_result = $episodes_stmt->get_result();

// 5. Fetch the specific episode to play
$current_episode_stmt = $conn->prepare("SELECT * FROM episodes WHERE series_id = ? AND episode_number = ?");
$current_episode_stmt->bind_param("ii", $series_id, $current_episode_number);
$current_episode_stmt->execute();
$current_episode_result = $current_episode_stmt->get_result();
$current_episode = $current_episode_result->fetch_assoc();


// Handle case where series or episode is not found
if (!$series || !$current_episode) {
    // You can create a more user-friendly "not found" page
    die("Error: Series or Episode not found.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch <?php echo htmlspecialchars($series['title']); ?> - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS remains unchanged */
        .watch-container { display: flex; flex-direction: column; min-height: 100vh; }
        .watch-content { display: grid; grid-template-columns: 250px 1fr 300px; flex: 1; gap: 0; }
        .episodes-sidebar { background-color: #1a1a1a; padding: 20px; border-right: 1px solid #333; overflow-y: auto; max-height: calc(100vh - 80px); }
        .episodes-title { color: #ff6b6b; font-size: 18px; margin-bottom: 15px; font-weight: 600; }
        .episode-list { list-style: none; padding: 0; margin: 0; }
        .episode-item { background-color: #2a2a2a; margin-bottom: 8px; border-radius: 6px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .episode-item:hover { background-color: #333; }
        .episode-item.active { background: linear-gradient(135deg, #6a4c93, #4a4a8a); border: 1px solid #ff6b6b; }
        .episode-link { display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; color: white; text-decoration: none; }
        .episode-info { display: flex; flex-direction: column; }
        .episode-number { font-weight: 600; margin-bottom: 2px; }
        .episode-title-sidebar { font-size: 12px; color: #aaaaaa; }
        .play-icon { color: #4CAF50; font-size: 16px; }
        .video-container { background-color: #000; display: flex; flex-direction: column; position: relative; }
        .video-player { width: 100%; height: 80vh; background: #000; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .video-controls { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 20px; display: flex; align-items: center; justify-content: space-between; }
        .control-left { display: flex; align-items: center; gap: 15px; }
        .play-pause-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }
        .volume-control { display: flex; align-items: center; gap: 5px; }
        .volume-slider { width: 80px; }
        .time-display { color: white; font-size: 14px; }
        .control-right { display: flex; align-items: center; gap: 15px; }
        .control-btn { background: none; border: none; color: white; font-size: 18px; cursor: pointer; padding: 5px; }
        .control-btn:hover { color: #ff6b6b; }
        .current-episode { background-color: #1f1f1f; color: white; padding: 10px 20px; font-weight: 600; border-top: 1px solid #333; }
        .anime-info-sidebar { background-color: #1a1a1a; padding: 20px; border-left: 1px solid #333; overflow-y: auto; max-height: calc(100vh - 80px); }
        .anime-poster { width: 100%; border-radius: 8px; margin-bottom: 15px; }
        .anime-title { color: white; font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .anime-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 15px; }
        .tag { background-color: #333; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; }
        .tag.rating { background-color: #4CAF50; }
        .tag.quality { background-color: #2196F3; }
        .synopsis { color: #cccccc; font-size: 14px; line-height: 1.5; margin-bottom: 15px; }
        .read-more { color: #ff6b6b; cursor: pointer; font-size: 12px; }
        .view-detail-btn { background-color: #ff6b6b; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; width: 100%; margin-bottom: 15px; text-align:center; display:block; text-decoration: none;}
        .rating-section { background-color: #2a2a2a; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
        .rating-display { color: #ffcc00; font-size: 18px; margin-bottom: 10px; }
        .vote-btn { background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        /* Add other styles from your file */
    </style>
</head>
<body>
    <div class="watch-container">
        <?php include 'header.php'; ?>
        
        <div class="watch-content">
        <aside class="episodes-sidebar">
            <h3 class="episodes-title">List of episodes:</h3>
            <ul class="episode-list">
                <?php while ($episode = $episodes_list_result->fetch_assoc()): ?>
                    <li class="episode-item <?php if ($episode['episode_number'] == $current_episode_number) echo 'active'; ?>">
                        <a href="watch.php?series_id=<?php echo $series_id; ?>&episode_number=<?php echo $episode['episode_number']; ?>" class="episode-link">
                            <div class="episode-info">
                                <div class="episode-number"><?php echo htmlspecialchars($episode['episode_number']); ?>. <?php echo htmlspecialchars($episode['title']); ?></div>
                            </div>
                            <i class="fas fa-play play-icon"></i>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>

        <main class="video-container">
            <div class="video-player">
                <video id="main-video" controls autoplay width="100%" height="100%">
                    <source src="assets/videos/<?php echo htmlspecialchars($current_episode['video_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="current-episode">
                 You are watching: <?php echo htmlspecialchars($series['title']); ?> - Episode <?php echo htmlspecialchars($current_episode_number); ?>
            </div>
        </main>

        <aside class="anime-info-sidebar">
             <img src="assets/images/<?php echo htmlspecialchars($series['image_path']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?> Poster" class="anime-poster">
            
            <h2 class="anime-title"><?php echo htmlspecialchars($series['title']); ?></h2>
            
            <div class="anime-tags">
                <span class="tag quality">HD</span>
                <?php foreach ($genres as $genre): ?>
                    <span class="tag"><?php echo htmlspecialchars($genre['name']); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="synopsis">
                <?php echo htmlspecialchars($series['description']); ?>
            </div>
            
            <a href="series_info.php?id=<?php echo $series_id; ?>" class="view-detail-btn">View detail</a>

            <div class="rating-section">
                <div class="rating-display">★ 0 (0 voted)</div>
                <button class="vote-btn">Vote now</button>
            </div>
        </aside>
        </div>
    </div>

    <section class="section">
            <div class="container">
            <h3 class="section-title">You might also like...</h3>
            <div class="cards">
                <?php
                // Fetch other series, excluding the current one
                $popular_sql = "SELECT * FROM series WHERE id != ? ORDER BY release_year DESC LIMIT 3";
                $popular_stmt = $conn->prepare($popular_sql);
                $popular_stmt->bind_param("i", $series_id);
                $popular_stmt->execute();
                $popular_result = $popular_stmt->get_result();

                while ($row = mysqli_fetch_assoc($popular_result)) {
                    // Assuming a generic card style and a series_info.php page for details
                    echo '<div class="movie-card" style="background-image: url(\'assets/images/' . htmlspecialchars($row['image_path']) . '\');">';
                    echo '<div class="card-content">';
                    echo '<div class="info-section">';
                    echo '<h3 class="card-title">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '</div>';
                    // Link to a series detail page
                    echo '<a href="series_info.php?id=' . $row['id'] . '" class="card-link"></a>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            </div>
        </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('main-video');

            // Episode selection highlighting is handled by PHP with the 'active' class
            const episodeItems = document.querySelectorAll('.episode-item');
            episodeItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Let the link navigation handle the page reload
                });
            });

            // The native video player has its own controls, so the custom JS for play/pause/volume is not strictly needed
            // unless you want to build a fully custom player UI. I've left the video tag with the `controls` attribute.
        });
    </script>
</body>
</html>