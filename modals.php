<div id="addMovieModal" class="modal">
    <div class="modal-content">
        <span class="close-btn movie-close">&times;</span>
        <h2 style="color: #ff6b6b; margin-bottom: 20px;">Add New Movie</h2>
        <form id="addMovieForm" method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="add_movie" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label for="movie-title">Title</label>
                    <input type="text" id="movie-title" name="movie_title" required>
                </div>
                <div class="form-group">
                    <label for="movie-title">Japnese</label>
                    <input type="text" id="movie-title" name="Japnese" required>
                </div>
                <div class="form-group">
                    <label for="movie-genre">Genre</label>
                    <select id="movie-genre" name="movie_genre" required>
                        <option value="">Select Genre</option>
                        <?php 
                        $genres = $conn->query("SELECT * FROM genres ORDER BY name");
                        while($g = $genres->fetch_assoc()) echo "<option value='{$g['id']}'>".htmlspecialchars($g['name'])."</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="movie-description">Description</label>
                    <textarea id="movie-description" name="movie_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="movie-release-year">Release Year</label>
                    <input type="number" id="movie-release-year" name="movie_release_year" placeholder="YYYY" required>
                </div>
                <div class="form-group">
                    <label for="movie-duration">Duration (minutes)</label>
                    <input type="number" id="movie-duration" name="movie_duration" required>
                </div>
                <div class="form-group full-width">
                    <label for="movie-image">Image</label>
                    <input type="file" id="movie-image" name="movie_image" accept="image/*" required>
                </div>
                <div class="form-group full-width">
                    <label for="movie-image">Movie</label>
                    <input type="file" id="movie-video" name="movie_video" accept="video/*" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-add-new">Save Movie</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="addSeriesModal" class="modal">
    <div class="modal-content">
        <span class="close-btn series-close">&times;</span>
        <h2 style="color: #ff6b6b; margin-bottom: 20px;">Add New Series</h2>
        <form id="addSeriesForm" method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="add_series" value="1">
            <div class="form-grid">
                <div class="form-group">
                    <label for="series-title">Title</label>
                    <input type="text" id="series-title" name="series_title" required>
                </div>
                <div class="form-group">
                    <label for="movie-title">Japnese</label>
                    <input type="text" id="series-title" name="Japnese" required>
                </div>
                <div class="form-group">
                    <label for="series-genre">Genre</label>
                    <select id="series-genre" name="series_genre" required>
                        <option value="">Select Genre</option>
                         <?php 
                        // Reset pointer and re-fetch genres
                        $genres->data_seek(0);
                        while($g = $genres->fetch_assoc()) echo "<option value='{$g['id']}'>".htmlspecialchars($g['name'])."</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="series-description">Description</label>
                    <textarea id="series-description" name="series_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="series-release-year">Release Year</label>
                    <input type="number" id="series-release-year" name="series_release_year" placeholder="YYYY" required>
                </div>
                <div class="form-group full-width">
                    <label for="series-image">Image</label>
                    <input type="file" id="series-image" name="series_image" accept="image/*" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-add-new">Save Series</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="addEpisodeModal" class="modal">
    <div class="modal-content">
        <span class="close-btn episode-close">&times;</span>
        <h2 style="color: #ff6b6b; margin-bottom: 20px;">Upload Episode</h2>
        <form id="addEpisodeForm" method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="add_episode" value="1">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="episode-series">Series</label>
                    <select id="episode-series" name="episode_series" required>
                        <option value="">Select a Series</option>
                        <?php 
                        $all_series = $conn->query("SELECT id, title FROM series ORDER BY title");
                        while($s = $all_series->fetch_assoc()) echo "<option value='{$s['id']}'>".htmlspecialchars($s['title'])."</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="episode-number">Episode Number</label>
                    <input type="number" id="episode-number" name="episode_number" required>
                </div>
                <div class="form-group">
                    <label for="episode-title">Episode Title</label>
                    <input type="text" id="episode-title" name="episode_title" required>
                </div>
                <div class="form-group full-width">
                    <label for="episode-video">Video File</label>
                    <input type="file" id="episode-video" name="episode_video" accept="video/*" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-add-new">Upload Episode</button>
                </div>
            </div>
        </form>
    </div>
</div>