<div id="addMovieModal" class="modal">
    <div class="modal-content">
        <span class="close-btn movie-close">&times;</span>
        <h2>Add New Movie</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_movie">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="movie_title">Title</label>
                    <input type="text" id="movie_title" name="movie_title" required>
                </div>
                <div class="form-group">
                    <label for="movie_genre">Genre</label>
                    <select id="movie_genre" name="movie_genre" required>
                        <?php
                        $genres = $conn->query("SELECT id, name FROM genres ORDER BY name");
                        while ($genre = $genres->fetch_assoc()) {
                            echo "<option value='{$genre['id']}'>{$genre['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="movie_release_year">Release Year</label>
                    <input type="number" id="movie_release_year" name="movie_release_year" required>
                </div>
                <div class="form-group">
                    <label for="movie_duration">Duration (minutes)</label>
                    <input type="number" id="movie_duration" name="movie_duration" required>
                </div>
                <div class="form-group full-width">
                    <label for="movie_description">Description</label>
                    <textarea id="movie_description" name="movie_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="movie_image">Image</label>
                    <input type="file" id="movie_image" name="movie_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="movie_thumbnail">ThumbNail</label>
                    <input type="file" id="movie_thumbnail" name="movie_thumbnail" accept="image/*" required>
                </div>
                <div class="form-group full-width">
                    <label for="movie-video">Movie</label>
                    <input type="file" id="movie-video" name="movie_video" accept=".mp4" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add-new">Add Movie</button>
            </div>
        </form>
    </div>
</div>

<div id="addSeriesModal" class="modal">
    <div class="modal-content">
        <span class="close-btn series-close">&times;</span>
        <h2>Add New Series</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_series">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="series_title">Title</label>
                    <input type="text" id="series_title" name="series_title" required>
                </div>
                <div class="form-group">
                    <label for="series_genre">Genre</label>
                    <select id="series_genre" name="series_genre" required>
                        <?php
                        $genres->data_seek(0); // Reset pointer
                        while ($genre = $genres->fetch_assoc()) {
                            echo "<option value='{$genre['id']}'>{$genre['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="series_release_year">Release Year</label>
                    <input type="number" id="series_release_year" name="series_release_year" required>
                </div>
                <div class="form-group">
                    <label for="series_image">Image</label>
                    <input type="file" id="series_image" name="series_image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="series_thumbnail">Thumbnail</label>
                    <input type="file" id="series_thumbnail" name="series_thumbnail" accept="image/*" required>
                </div>
                <div class="form-group full-width">
                    <label for="series_description">Description</label>
                    <textarea id="series_description" name="series_description" required></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add-new">Add Series</button>
            </div>
        </form>
    </div>
</div>

<div id="addEpisodeModal" class="modal">
    <div class="modal-content">
        <span class="close-btn episode-close">&times;</span>
        <h2 id="episodeModalTitle">Upload New Episode</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="save_episode" value="1">
            <input type="hidden" id="episode_id" name="episode_id">
            <div class="form-group full-width">
                <label for="episode_series">Select Series</label>
                <select id="episode_series" name="episode_series" required>
                    <option value="" disabled selected>Select a series</option>
                    <?php
                    $series_list = $conn->query("SELECT id, title FROM series ORDER BY title");
                    while ($series = $series_list->fetch_assoc()) {
                        echo "<option value='{$series['id']}'>{$series['title']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="episodes_number">Episode Number</label>
                    <input type="number" id="episodes_number" name="episode_number" required>
                </div>
                <div class="form-group">
                    <label for="episodes_title">Episode Title</label>
                    <input type="text" id="episodes_title" name="episode_title" required>
                </div>
            </div>
            <div class="form-group full-width">
                <label for="episodes_video">Video File</label>
                <input type="file" id="episodes_video" name="episode_video" accept="video/*" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add-new" id="episodeSubmitBtn">Upload Episode</button>
            </div>
        </form>
    </div>
</div>

<div id="editMovieModal" class="modal">
    <div class="modal-content">
        <span class="close-btn edit-movie-close">&times;</span>
        <h2>Edit Movie</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_movie">
            <input type="hidden" id="edit_movie_id" name="movie_id">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="edit_movie_title">Title</label>
                    <input type="text" id="edit_movie_title" name="movie_title" required>
                </div>
                <div class="form-group">
                    <label for="edit_movie_genre">Genre</label>
                    <select id="edit_movie_genre" name="movie_genre" required>
                         <?php
                        $genres->data_seek(0);
                        while ($genre = $genres->fetch_assoc()) {
                            echo "<option value='{$genre['id']}'>{$genre['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_movie_release_year">Release Year</label>
                    <input type="number" id="edit_movie_release_year" name="movie_release_year" required>
                </div>
                <div class="form-group">
                    <label for="edit_movie_duration">Duration (minutes)</label>
                    <input type="number" id="edit_movie_duration" name="movie_duration" required>
                </div>
                <div class="form-group full-width">
                    <label for="edit_movie_description">Description</label>
                    <textarea id="edit_movie_description" name="movie_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_movie_image">New Image (optional)</label>
                    <input type="file" id="edit_movie_image" name="movie_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="edit_movie_thumbnail">New ThumbNail (optional)</label>
                    <input type="file" id="edit_movie_thumbnail" name="movie_thumbnail" accept="image/*">
                </div>
                <div class="form-group full-width">
                    <label for="movie-video">New Movie (optional)</label>
                    <input type="file" id="movie-video" name="movie_video" accept=".mp4">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add-new">Update Movie</button>
            </div>
        </form>
    </div>
</div>

<div id="editSeriesModal" class="modal">
    <div class="modal-content">
        <span class="close-btn edit-series-close">&times;</span>
        <h2>Edit Series</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_series">
            <input type="hidden" id="edit_series_id" name="series_id">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="edit_series_title">Title</label>
                    <input type="text" id="edit_series_title" name="series_title" required>
                </div>
                <div class="form-group">
                    <label for="edit_series_genre">Genre</label>
                    <select id="edit_series_genre" name="series_genre" required>
                        <?php
                        $genres->data_seek(0);
                        while ($genre = $genres->fetch_assoc()) {
                            echo "<option value='{$genre['id']}'>{$genre['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_series_release_year">Release Year</label>
                    <input type="number" id="edit_series_release_year" name="series_release_year" required>
                </div>
                <div class="form-group full-width">
                    <label for="edit_series_description">Description</label>
                    <textarea id="edit_series_description" name="series_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_series_image">New Image (optional)</label>
                    <input type="file" id="edit_series_image" name="series_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="edit_series_thumbnail">New ThumbNail (optional)</label>
                    <input type="file" id="edit_series_thumbnail" name="series_thumbnail" accept="image/*">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-add-new">Update Series</button>
            </div>
        </form>
    </div>
</div>
