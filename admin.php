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

// --- AJAX: FETCH MOVIE/SERIES DETAILS ---
if (isset($_GET['get_details'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    $data = null;

    if ($type === 'movie') {
        $stmt = $conn->prepare("SELECT m.title, m.description, m.release_year, m.duration, mg.genre_id FROM movies m JOIN movie_genres mg ON m.id = mg.movie_id WHERE m.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    } elseif ($type === 'series') {
        $stmt = $conn->prepare("SELECT s.title, s.description, s.release_year, sg.genre_id FROM series s JOIN series_genres sg ON s.id = sg.series_id WHERE s.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


$message = ''; // To store success or error messages

// --- HELPER FUNCTION FOR FILE UPLOADS ---
function handle_upload($file_input_name, $target_dir) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        // Sanitize the filename to prevent security issues
        $safe_filename = preg_replace("/[^a-zA-Z0-9-_\.]/", "", basename($_FILES[$file_input_name]['name']));
        $filename = uniqid() . '_' . $safe_filename;
        
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
    // First, get the image and video paths to delete the files
    $stmt = $conn->prepare("SELECT image_path, video_path FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(!empty($row['image_path']) && file_exists('assets/images/' . $row['image_path'])){
            unlink('assets/images/' . $row['image_path']);
        }
        if(!empty($row['video_path']) && file_exists('assets/videos/' . $row['video_path'])){
            unlink('assets/videos/' . $row['video_path']);
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
    $video_path = handle_upload('movie_video', 'assets/videos/');

    if ($image_path && $video_path) {
        $created_by = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $stmt_movie = $conn->prepare("INSERT INTO movies (title, description, release_year, duration, image_path, video_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_movie->bind_param("ssiisssi", $title, $description, $release_year, $duration, $image_path, $video_path, $created_by);
        
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
        $message = "Error uploading image or video file.";
    }
}

// --- UPDATE MOVIE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['movie_title'];
    $genre_id = $_POST['movie_genre'];
    $description = $_POST['movie_description'];
    $release_year = $_POST['movie_release_year'];
    $duration = $_POST['movie_duration'];

    $image_path = handle_upload('movie_image', 'assets/images/');
    $video_path = handle_upload('movie_video', 'assets/videos/');

    $params = [];
    $types = "";

    $sql = "UPDATE movies SET title = ?, description = ?, release_year = ?, duration = ?";
    array_push($params, $title, $description, $release_year, $duration);
    $types .= "ssii";

    if ($image_path) {
        $sql .= ", image_path = ?";
        array_push($params, $image_path);
        $types .= "s";
    }
    if ($video_path) {
        $sql .= ", video_path = ?";
        array_push($params, $video_path);
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    array_push($params, $movie_id);
    $types .= "i";

    $stmt_movie = $conn->prepare($sql);
    $stmt_movie->bind_param($types, ...$params);

    if ($stmt_movie->execute()) {
        $stmt_genre = $conn->prepare("UPDATE movie_genres SET genre_id = ? WHERE movie_id = ?");
        $stmt_genre->bind_param("ii", $genre_id, $movie_id);
        $stmt_genre->execute();
        $message = "Movie updated successfully!";
    } else {
        $message = "Error updating movie: " . $stmt_movie->error;
    }
    $stmt_movie->close();
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

// --- UPDATE SERIES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_series'])) {
    $series_id = $_POST['series_id'];
    $title = $_POST['series_title'];
    $genre_id = $_POST['series_genre'];
    $description = $_POST['series_description'];
    $release_year = $_POST['series_release_year'];
    
    $image_path = handle_upload('series_image', 'assets/images/');

    $sql = "UPDATE series SET title = ?, description = ?, release_year = ?" . ($image_path ? ", image_path = ?" : "") . " WHERE id = ?";
    $stmt_series = $conn->prepare($sql);
    
    if ($image_path) {
        $stmt_series->bind_param("ssisi", $title, $description, $release_year, $image_path, $series_id);
    } else {
        $stmt_series->bind_param("ssii", $title, $description, $release_year, $series_id);
    }

    if ($stmt_series->execute()) {
        $stmt_genre = $conn->prepare("UPDATE series_genres SET genre_id = ? WHERE series_id = ?");
        $stmt_genre->bind_param("ii", $genre_id, $series_id);
        $stmt_genre->execute();
        $message = "Series updated successfully!";
    } else {
        $message = "Error updating series: " . $stmt_series->error;
    }
    $stmt_series->close();
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
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>

    <?php include "header.php"; ?>

    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
            <nav class="admin-nav">
                <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_movies.php"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="manage_series.php"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
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

    <script src="admin.js"></script>
</body>
</html>