<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Kouryuu Densetsu Villgust - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>

        .watch-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .watch-content {
            display: grid;
            grid-template-columns: 250px 1fr 300px;
            flex: 1;
            gap: 0;
        }


        /* Left Sidebar - Episodes */
        .episodes-sidebar {
            background-color: #1a1a1a;
            padding: 20px;
            border-right: 1px solid #333;
            overflow-y: auto;
            max-height: calc(100vh - 80px);
        }

        .episodes-title {
            color: #ff6b6b;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }


        .episode-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .episode-item {
            background-color: #2a2a2a;
            margin-bottom: 8px;
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .episode-item:hover {
            background-color: #333;
        }

        .episode-item.active {
            background: linear-gradient(135deg, #6a4c93, #4a4a8a);
            border: 1px solid #ff6b6b;
        }

        .episode-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
        }

        .episode-info {
            display: flex;
            flex-direction: column;
        }

        .episode-number {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .episode-title {
            font-size: 12px;
            color: #aaaaaa;
        }

        .play-icon {
            color: #4CAF50;
            font-size: 16px;
        }

        /* Center - Video Player */
        .video-container {
            background-color: #000;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .video-player {
            width: 100%;
            height: 80vh;
            background: linear-gradient(45deg, #4a4a8a, #6a4c93, #8e44ad);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .video-placeholder {
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.3) 70%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .video-placeholder::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: conic-gradient(from 0deg, transparent, #ff6b6b, transparent);
            border-radius: 50%;
            animation: spin 3s linear infinite;
        }

        .video-placeholder::after {
            content: '▶';
            position: absolute;
            font-size: 60px;
            color: white;
            z-index: 2;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .video-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .control-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .play-pause-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .volume-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .volume-slider {
            width: 80px;
        }

        .time-display {
            color: white;
            font-size: 14px;
        }

        .control-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .control-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
        }

        .control-btn:hover {
            color: #ff6b6b;
        }


        .current-episode {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: 600;
        }


        /* Right Sidebar - Anime Info */
        .anime-info-sidebar {
            background-color: #1a1a1a;
            padding: 20px;
            border-left: 1px solid #333;
            overflow-y: auto;
            max-height: calc(100vh - 80px);
        }

        .anime-poster {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .anime-title {
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .anime-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }

        .tag {
            background-color: #ff6b6b;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .tag.rating {
            background-color: #4CAF50;
        }

        .tag.quality {
            background-color: #2196F3;
        }

        .synopsis {
            color: #cccccc;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .read-more {
            color: #ff6b6b;
            cursor: pointer;
            font-size: 12px;
        }


        .view-detail-btn {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 15px;
        }

        .rating-section {
            background-color: #2a2a2a;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .rating-display {
            color: #ffcc00;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .vote-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }


        /* Responsive Design */
        @media (max-width: 1024px) {
            .watch-content {
                grid-template-columns: 250px 1fr 250px;
            }
        }

        @media (max-width: 768px) {
            .watch-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr auto;
            }

            .episodes-sidebar {
                order: 3;
                max-height: 200px;
                border-right: none;
                border-top: 1px solid #333;
            }

            .anime-info-sidebar {
                order: 2;
                max-height: 300px;
                border-left: none;
                border-top: 1px solid #333;
            }

            .video-container {
                order: 1;
            }

            .video-player {
                height: 50vh;
            }

            .header-left {
                gap: 10px;
            }

            .search-container {
                min-width: 200px;
            }

            .header-right {
                gap: 10px;
            }

            .social-icons {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .watch-header {
                padding: 10px;
                flex-direction: column;
                gap: 10px;
            }

            .header-left,
            .header-right {
                width: 100%;
                justify-content: center;
            }

            .search-container {
                min-width: 150px;
            }

            .video-player {
                height: 50vh;
            }

            .options-row {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="watch-container">
        <!-- Header -->
        <?php include 'header.php'; ?>
        <?php include 'db_connect.php'; ?>

        <div class="watch-content">
        <!-- Left Sidebar - Episodes -->
        <aside class="episodes-sidebar">
            <h3 class="episodes-title">List of episodes:</h3>
            <ul class="episode-list">
                <li class="episode-item active">
                    <a href="#" class="episode-link">
                        <div class="episode-info">
                            <div class="episode-number">1 Episode 1</div>
                        </div>
                        <i class="fas fa-play play-icon"></i>
                    </a>
                </li>
                <li class="episode-item">
                    <a href="#" class="episode-link">
                        <div class="episode-info">
                            <div class="episode-number">2 Episode 2</div>
                        </div>
                        <i class="fas fa-play play-icon"></i>
                    </a>
                </li>
                <li class="episode-item">
                    <a href="#" class="episode-link">
                        <div class="episode-info">
                            <div class="episode-number">3 Episode 3</div>
                        </div>
                        <i class="fas fa-play play-icon"></i>
                    </a>
                </li>
                <li class="episode-item">
                    <a href="#" class="episode-link">
                        <div class="episode-info">
                            <div class="episode-number">4 Episode 4</div>
                        </div>
                        <i class="fas fa-play play-icon"></i>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Center - Video Player -->
        <main class="video-container">
            <div class="video-player">
                <div class="video-placeholder"></div>
                <div class="video-controls">
                    <div class="control-left">
                        <button class="play-pause-btn">
                            <i class="fas fa-pause"></i>
                        </button>
                        <div class="volume-control">
                            <i class="fas fa-volume-up"></i>
                            <input type="range" class="volume-slider" min="0" max="100" value="50">
                        </div>
                        <div class="time-display">00:10 / 28:35</div>
                    </div>
                    <div class="control-right">
                        <button class="control-btn" title="Rewind 10s">
                            <i class="fas fa-backward"></i>
                        </button>
                        <button class="control-btn" title="Forward 10s">
                            <i class="fas fa-forward"></i>
                        </button>
                        <button class="control-btn" title="Picture in Picture">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                        <button class="control-btn" title="Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="control-btn" title="Settings">
                            <i class="fas fa-cog"></i>
                        </button>
                        <button class="control-btn" title="Subtitles">
                            <i class="fas fa-closed-captioning"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="current-episode">
                You are watching Episode 1.
            </div>
        </main>

        <!-- Right Sidebar - Anime Info -->
        <aside class="anime-info-sidebar">
            <img src="https://via.placeholder.com/250x350/6a4c93/ffffff?text=Villgust" alt="Anime Poster" class="anime-poster">
            <h2 class="anime-title">Kouryuu Densetsu Villgust</h2>
            <div class="anime-tags">
                <span class="tag rating">PG-13</span>
                <span class="tag quality">HD</span>
                <span class="tag">cc 2</span>
                <span class="tag">OVA</span>
                <span class="tag">28m</span>
            </div>
            <div class="synopsis">
                Villgust is a peaceful world that exists parallel to ours. However, now an evil deity has been revived and has sent evil creatures to destroy many countries, which left darkness and terror to rule the world. The peoples' prayers and cries has reached out to the gods, and they have chosen five brave warriors to fight against the evil forces... <span class="read-more">+ More</span>
            </div>
           
            <button class="view-detail-btn">View detail</button>
            <div class="rating-section">
                <div class="rating-display">★ 0 (0 voted)</div>
                <button class="vote-btn">Vote now</button>
            </div>
            
        </aside>
        </div>
    </div>

    <section class="section">
            <div class="container">
            <h3 class="section-title">Popular Animes</h3>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Episode selection
            const episodeItems = document.querySelectorAll('.episode-item');
            episodeItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    episodeItems.forEach(ep => ep.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Play/Pause functionality
            const playPauseBtn = document.querySelector('.play-pause-btn');
            const videoPlaceholder = document.querySelector('.video-placeholder');
            let isPlaying = false;

            playPauseBtn.addEventListener('click', function() {
                isPlaying = !isPlaying;
                const icon = this.querySelector('i');
                if (isPlaying) {
                    icon.className = 'fas fa-pause';
                    videoPlaceholder.style.animationPlayState = 'running';
                } else {
                    icon.className = 'fas fa-play';
                    videoPlaceholder.style.animationPlayState = 'paused';
                }
            });

            


            // Read more functionality
            const readMore = document.querySelector('.read-more');
            const synopsis = document.querySelector('.synopsis');
            
            // Define the full text (without the read-more span)
            const fullText = "Villgust is a peaceful world that exists parallel to ours. However, now an evil deity has been revived and has sent evil creatures to destroy many countries, which left darkness and terror to rule the world. The peoples' prayers and cries has reached out to the gods, and they have chosen five brave warriors to fight against the evil forces.";
            
            // Define the short text
            const shortText = fullText.substring(0, 300) + '...';
            
            // Set initial state to short text
            synopsis.innerHTML = shortText + ' <span class="read-more">+ More</span>';
            
            // Update the readMore reference after changing innerHTML
            const readMoreBtn = synopsis.querySelector('.read-more');

            readMoreBtn.addEventListener('click', function() {
                if (this.textContent === '+ More') {
                    synopsis.innerHTML = fullText + ' <span class="read-more">- Less</span>';
                } else {
                    synopsis.innerHTML = shortText + ' <span class="read-more">+ More</span>';
                }
            });

            // Vote functionality
            const voteBtn = document.querySelector('.vote-btn');
            voteBtn.addEventListener('click', function() {
                this.textContent = 'Voted!';
                this.style.backgroundColor = '#ff6b6b';
                this.disabled = true;
            });

            // Volume control
            const volumeSlider = document.querySelector('.volume-slider');
            const volumeIcon = document.querySelector('.volume-control i');
            
            volumeSlider.addEventListener('input', function() {
                const volume = this.value;
                if (volume == 0) {
                    volumeIcon.className = 'fas fa-volume-mute';
                } else if (volume < 50) {
                    volumeIcon.className = 'fas fa-volume-down';
                } else {
                    volumeIcon.className = 'fas fa-volume-up';
                }
            });
        });
    </script>
</body>
</html>
