<?php
// Ensure the user is an admin, otherwise redirect them.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Enforce admin-only access
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: index.php");
    exit;
}

include "db_connect.php"; // Include your database connection

$message = ''; // To store success or error messages

// --- HELPER FUNCTION FOR FILE UPLOADS ---
function handle_upload($file_input_name, $target_dir) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $filename = uniqid() . '_' . basename($_FILES[$file_input_name]['name']);
        $target_path = $target_dir . $filename;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_path)) {
            return $filename;
        }
    }
    return null;
}

// --- DELETE MOVIE ---
if (isset($_GET['delete_movie'])) {
    $id_to_delete = intval($_GET['delete_movie']);
    // First, get the image path to delete the file
    $stmt = $conn->prepare("SELECT image_path FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(!empty($row['image_path']) && file_exists('assets/images/' . $row['image_path'])){
            unlink('assets/images/' . $row['image_path']);
        }
    }
    $stmt->close();
    
    // Now, delete the record from the database
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        $message = "Movie deleted successfully!";
    } else {
        $message = "Error deleting movie: " . $stmt->error;
    }
    $stmt->close();
}

// --- DELETE SERIES ---
if (isset($_GET['delete_series'])) {
    $id_to_delete = intval($_GET['delete_series']);
     // First, get the image path to delete the file
    $stmt = $conn->prepare("SELECT image_path FROM series WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(!empty($row['image_path']) && file_exists('assets/images/' . $row['image_path'])){
            unlink('assets/images/' . $row['image_path']);
        }
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM series WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        $message = "Series deleted successfully!";
    } else {
        $message = "Error deleting series: " . $stmt->error;
    }
    $stmt->close();
}


// --- ADD NEW MOVIE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_movie'])) {
    $title = $_POST['movie_title'];
    $genre_id = $_POST['movie_genre'];
    $description = $_POST['movie_description'];
    $release_year = $_POST['movie_release_year'];
    $duration = $_POST['movie_duration'];
    $image_path = handle_upload('movie_image', 'assets/images/');

    if ($image_path) {
        $created_by = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $stmt_movie = $conn->prepare("INSERT INTO movies (title, description, release_year, duration, image_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_movie->bind_param("ssiisi", $title, $description, $release_year, $duration, $image_path, $created_by);
        if ($stmt_movie->execute()) {
            $last_movie_id = $conn->insert_id;
            $stmt_genre = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
            $stmt_genre->bind_param("ii", $last_movie_id, $genre_id);
            if ($stmt_genre->execute()) {
                $message = "New movie added successfully!";
            } else {
                $message = "Error linking movie to genre: " . $stmt_genre->error;
            }
            $stmt_genre->close();
        } else {
            $message = "Error adding movie: " . $stmt_movie->error;
        }
        $stmt_movie->close();
    } else {
        $message = "Error uploading image.";
    }
}

// --- EDIT MOVIE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_movie'])) {
    $movie_id = intval($_POST['movie_id']);
    $title = $_POST['edit_movie_title'];
    $genre_id = $_POST['edit_movie_genre'];
    $description = $_POST['edit_movie_description'];
    $release_year = $_POST['edit_movie_release_year'];
    $duration = $_POST['edit_movie_duration'];

    // Handle image upload if a new one is provided
    if (isset($_FILES['edit_movie_image']) && $_FILES['edit_movie_image']['error'] == 0) {
        $image_path = handle_upload('edit_movie_image', 'assets/images/');
        $stmt = $conn->prepare("UPDATE movies SET title=?, description=?, release_year=?, duration=?, image_path=? WHERE id=?");
        $stmt->bind_param("ssiisi", $title, $description, $release_year, $duration, $image_path, $movie_id);
    } else {
        $stmt = $conn->prepare("UPDATE movies SET title=?, description=?, release_year=?, duration=? WHERE id=?");
        $stmt->bind_param("ssiii", $title, $description, $release_year, $duration, $movie_id);
    }

    if ($stmt->execute()) {
        // Update the genre link in the junction table
        $stmt_genre = $conn->prepare("UPDATE movie_genres SET genre_id=? WHERE movie_id=?");
        $stmt_genre->bind_param("ii", $genre_id, $movie_id);
        $stmt_genre->execute();
        $stmt_genre->close();
        $message = "Movie updated successfully!";
    } else {
        $message = "Error updating movie: " . $stmt->error;
    }
    $stmt->close();
}


// --- ADD NEW SERIES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_series'])) {
    $title = $_POST['series_title'];
    $genre_id = $_POST['series_genre'];
    $description = $_POST['series_description'];
    $release_year = $_POST['series_release_year'];
    $image_path = handle_upload('series_image', 'assets/images/');

    if ($image_path) {
        $created_by = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $stmt_series = $conn->prepare("INSERT INTO series (title, description, release_year, image_path, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt_series->bind_param("ssisi", $title, $description, $release_year, $image_path, $created_by);
        if ($stmt_series->execute()) {
            $last_series_id = $conn->insert_id;
            $stmt_genre = $conn->prepare("INSERT INTO series_genres (series_id, genre_id) VALUES (?, ?)");
            $stmt_genre->bind_param("ii", $last_series_id, $genre_id);
            if ($stmt_genre->execute()) {
                $message = "New series added successfully!";
            } else {
                $message = "Error linking series to genre: " . $stmt_genre->error;
            }
            $stmt_genre->close();
        } else {
            $message = "Error adding series: " . $stmt_series->error;
        }
        $stmt_series->close();
    } else {
        $message = "Error uploading image for the series.";
    }
}

// --- EDIT SERIES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_series'])) {
    $series_id = intval($_POST['series_id']);
    $title = $_POST['edit_series_title'];
    $genre_id = $_POST['edit_series_genre'];
    $description = $_POST['edit_series_description'];
    $release_year = $_POST['edit_series_release_year'];

    if (isset($_FILES['edit_series_image']) && $_FILES['edit_series_image']['error'] == 0) {
        $image_path = handle_upload('edit_series_image', 'assets/images/');
        $stmt = $conn->prepare("UPDATE series SET title=?, description=?, release_year=?, image_path=? WHERE id=?");
        $stmt->bind_param("ssisi", $title, $description, $release_year, $image_path, $series_id);
    } else {
        $stmt = $conn->prepare("UPDATE series SET title=?, description=?, release_year=? WHERE id=?");
        $stmt->bind_param("ssii", $title, $description, $release_year, $series_id);
    }

    if ($stmt->execute()) {
        $stmt_genre = $conn->prepare("UPDATE series_genres SET genre_id=? WHERE series_id=?");
        $stmt_genre->bind_param("ii", $genre_id, $series_id);
        $stmt_genre->execute();
        $stmt_genre->close();
        $message = "Series updated successfully!";
    } else {
        $message = "Error updating series: " . $stmt->error;
    }
    $stmt->close();
}


// --- ADD NEW EPISODE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_episode'])) {
    $series_id = $_POST['episode_series'];
    $episode_number = $_POST['episode_number'];
    $title = $_POST['episode_title'];
    $video_path = handle_upload('episode_video', 'assets/videos/');

    if ($video_path) {
        $stmt = $conn->prepare("INSERT INTO episodes (series_id, episode_number, title, video_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $series_id, $episode_number, $title, $video_path);
        if ($stmt->execute()) {
            $message = "New episode uploaded successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Error uploading episode video.";
    }
}


// --- DASHBOARD STATISTICS ---
$total_movies = $conn->query("SELECT COUNT(id) as count FROM movies")->fetch_assoc()['count'];
$total_series = $conn->query("SELECT COUNT(id) as count FROM series")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Admin Page Specific Styles */
        .admin-wrapper { display: flex; min-height: calc(100vh - 70px); }
        .admin-sidebar { width: 250px; background-color: #0f0f0f; padding: 20px; display: flex; flex-direction: column; }
        .admin-sidebar h2 { color: #ff6b6b; text-align: center; margin-bottom: 30px; }
        .admin-nav a { display: block; color: #fff; padding: 15px 20px; text-decoration: none; border-radius: 6px; margin-bottom: 10px; transition: background-color 0.3s, color 0.3s; display: flex; align-items: center; gap: 15px; }
        .admin-nav a:hover, .admin-nav a.active { background-color: #ff6b6b; color: #fff; }
        .admin-nav a i { width: 20px; text-align: center; }
        .admin-main-content { flex-grow: 1; padding: 30px; background-color: #1a1a1a; }
        .dashboard-header { margin-bottom: 30px; }
        .dashboard-header h1 { font-size: 2.5rem; color: #ffffff; }
        .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background-color: #2a2a2a; padding: 25px; border-radius: 8px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #ff6b6b; }
        .stat-card-icon { font-size: 3rem; color: #ff6b6b; }
        .stat-card-info h3 { margin: 0 0 5px 0; font-size: 2rem; color: #fff; }
        .stat-card-info p { margin: 0; color: #aaaaaa; }
        .content-table-section { margin-bottom: 40px; }
        .content-table-section .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .content-table-section h2 { font-size: 1.8rem; color: #ff6b6b; margin: 0; }
        .content-table { width: 100%; border-collapse: collapse; background-color: #2a2a2a; border-radius: 8px; overflow: hidden; }
        .content-table th, .content-table td { padding: 15px; text-align: left; border-bottom: 1px solid #333; }
        .content-table th { background-color: #1f1f1f; }
        .content-table td.actions a { color: #fff; margin-right: 15px; font-size: 1.1rem; }
        .content-table td.actions a.edit { color: #4CAF50; }
        .content-table td.actions a.delete { color: #e55a5a; }
        .btn-add-new { background-color: #ff6b6b; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.3s; }
        .btn-add-new:hover { background-color: #e55a5a; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
        .modal-content { background-color: #2a2a2a; margin: auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; position: relative; }
        .close-btn { color: #aaaaaa; position: absolute; top: 15px; right: 20px; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover, .close-btn:focus { color: #ff6b6b; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { margin-bottom: 8px; color: #cccccc; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; background-color: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 4px; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-actions { grid-column: 1 / -1; text-align: right; margin-top: 20px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; font-weight: bold; text-align: center; }
        .message.success { background-color: #4CAF50; }
        .message.error { background-color: #e55a5a; }
    </style>
</head>
<body>

    <?php include "header.php"; ?>

    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
            <nav class="admin-nav">
                <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="#"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="#"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="#"><i class="fas fa-users"></i> Manage Users</a>
            </nav>
        </aside>

        <main class="admin-main-content">
            <div class="dashboard-header"><h1>Dashboard</h1></div>
            <?php if (!empty($message)): ?>
                <div class="message <?php echo (strpos($message, 'Error') === false) ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <section class="dashboard-stats">
                 <div class="stat-card">
                    <div class="stat-card-icon"><i class="fas fa-film"></i></div>
                    <div class="stat-card-info">
                        <h3><?php echo $total_movies; ?></h3><p>Total Movies</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon"><i class="fas fa-tv"></i></div>
                    <div class="stat-card-info">
                        <h3><?php echo $total_series; ?></h3><p>Total Series</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-card-info">
                        <h3><?php echo $total_users; ?></h3><p>Registered Users</p>
                    </div>
                </div>
            </section>

            <section class="content-table-section">
                <div class="section-header">
                    <h2>Recently Added Movies</h2>
                    <button id="addMovieBtn" class="btn-add-new"><i class="fas fa-plus"></i> Add New Movie</button>
                </div>
                <table class="content-table">
                    <thead><tr><th>Title</th><th>Genre</th><th>Release Year</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                            $sql_movies = "SELECT m.id, m.title, m.release_year, GROUP_CONCAT(g.name SEPARATOR ', ') as genres FROM movies m LEFT JOIN movie_genres mg ON m.id = mg.movie_id LEFT JOIN genres g ON mg.genre_id = g.id GROUP BY m.id ORDER BY m.id DESC LIMIT 5";
                            $movie_result = $conn->query($sql_movies);
                            if ($movie_result && $movie_result->num_rows > 0) {
                                while($row = $movie_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['genres']); ?></td>
                                <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                                <td class="actions">
                                    <a href="#" class="edit-movie" data-id="<?php echo $row['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="admin.php?delete_movie=<?php echo $row['id']; ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this movie?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; } else { echo "<tr><td colspan='4'>No movies found.</td></tr>"; } ?>
                    </tbody>
                </table>
            </section>

            <section class="content-table-section">
                <div class="section-header">
                    <h2>Recently Added Series</h2>
                    <div>
                        <button id="addSeriesBtn" class="btn-add-new"><i class="fas fa-plus"></i> Add New Series</button>
                        <button id="addEpisodeBtn" class="btn-add-new" style="margin-left: 10px;"><i class="fas fa-upload"></i> Upload Episode</button>
                    </div>
                </div>
                 <table class="content-table">
                    <thead><tr><th>Title</th><th>Genre</th><th>Release Year</th><th>Actions</th></tr></thead>
                    <tbody>
                         <?php
                            $sql_series = "SELECT s.id, s.title, s.release_year, GROUP_CONCAT(g.name SEPARATOR ', ') as genres FROM series s LEFT JOIN series_genres sg ON s.id = sg.series_id LEFT JOIN genres g ON sg.genre_id = g.id GROUP BY s.id ORDER BY s.id DESC LIMIT 5";
                            $series_result = $conn->query($sql_series);
                             if ($series_result && $series_result->num_rows > 0) {
                                while($row = $series_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['genres']); ?></td>
                            <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                            <td class="actions">
                                <a href="#" class="edit-series" data-id="<?php echo $row['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="admin.php?delete_series=<?php echo $row['id']; ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this series?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; } else { echo "<tr><td colspan='4'>No series found.</td></tr>"; } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>


    <?php include "modals.php" ?>

    <?php include "footer.php"; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Movie Modal
            const movieModal = document.getElementById("addMovieModal");
            const addMovieBtn = document.getElementById("addMovieBtn");
            const movieCloseBtn = document.querySelector(".movie-close");

            addMovieBtn.onclick = () => movieModal.style.display = "flex";
            movieCloseBtn.onclick = () => movieModal.style.display = "none";
            
            // Series Modal
            const seriesModal = document.getElementById("addSeriesModal");
            const addSeriesBtn = document.getElementById("addSeriesBtn");
            const seriesCloseBtn = document.querySelector(".series-close");

            addSeriesBtn.onclick = () => seriesModal.style.display = "flex";
            seriesCloseBtn.onclick = () => seriesModal.style.display = "none";

            // Episode Modal
            const episodeModal = document.getElementById("addEpisodeModal");
            const addEpisodeBtn = document.getElementById("addEpisodeBtn");
            const episodeCloseBtn = document.querySelector(".episode-close");

            addEpisodeBtn.onclick = () => episodeModal.style.display = "flex";
            episodeCloseBtn.onclick = () => episodeModal.style.display = "none";


            // Close modals if outside is clicked
            window.onclick = function(event) {
                if (event.target == movieModal) {
                    movieModal.style.display = "none";
                }
                if (event.target == seriesModal) {
                    seriesModal.style.display = "none";
                }
                if (event.target == episodeModal) {
                    episodeModal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>