<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: ../public/index.php");
    exit;
}

include "../includes/db_connect.php";

$message = '';

function handle_upload($file_input_name, $target_dir) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_movie'])) {
        $title = $_POST['movie_title'];
        $genre_id = $_POST['movie_genre'];
        $description = $_POST['movie_description'];
        $release_year = $_POST['movie_release_year'];
        $duration = $_POST['movie_duration'];
        
        $image_path = handle_upload('movie_image', '../assets/images/');
        $thumbnail_path = handle_upload('movie_thumbnail', '../assets/thumbnail/');
        $video_path = handle_upload('movie_video', '../assets/videos/');

        if ($image_path && $video_path) {
            $created_by = $_SESSION['user']['id'];
            $stmt_movie = $conn->prepare("INSERT INTO movies (title, description, release_year, duration, image_path, thumbnail_path, video_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_movie->bind_param("ssiisssi", $title, $description, $release_year, $duration, $image_path, $thumbnail_path, $video_path, $created_by);
            
            if ($stmt_movie->execute()) {
                $last_movie_id = $conn->insert_id;
                $stmt_genre = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                $stmt_genre->bind_param("ii", $last_movie_id, $genre_id);
                $stmt_genre->execute();
                $message = "New movie added successfully!";
            } else {
                $message = "Error adding movie.";
            }
        } else {
            $message = "Error uploading files.";
        }
    }

    if (isset($_POST['update_movie'])) {
        $movie_id = $_POST['movie_id'];
        $title = $_POST['movie_title'];
        $genre_id = $_POST['movie_genre'];
        $description = $_POST['movie_description'];
        $release_year = $_POST['movie_release_year'];
        $duration = $_POST['movie_duration'];

        $image_path = handle_upload('movie_image', '../assets/images/');
        $thumbnail_path = handle_upload('movie_thumbnail', '../assets/thumbnail/');
        $video_path = handle_upload('movie_video', '../assets/videos/');
        
        $sql = "UPDATE movies SET title = ?, description = ?, release_year = ?, duration = ?";
        $params = ["ssii", $title, $description, $release_year, $duration];
        
        if ($image_path) {
            $sql .= ", image_path = ?";
            $params[0] .= "s";
            $params[] = $image_path;
        }
        if ($thumbnail_path) {
            $sql .= ", thumbnail_path = ?";
            $params[0] .= "s";
            $params[] = $thumbnail_path;
        }
        if ($video_path) {
            $sql .= ", video_path = ?";
            $params[0] .= "s";
            $params[] = $video_path;
        }
        
        $sql .= " WHERE id = ?";
        $params[0] .= "i";
        $params[] = $movie_id;
        
        $stmt_movie = $conn->prepare($sql);
        $stmt_movie->bind_param(...$params);

        if ($stmt_movie->execute()) {
            $stmt_genre = $conn->prepare("UPDATE movie_genres SET genre_id = ? WHERE movie_id = ?");
            $stmt_genre->bind_param("ii", $genre_id, $movie_id);
            $stmt_genre->execute();
            $message = "Movie updated successfully!";
        } else {
            $message = "Error updating movie.";
        }
    }

    if (isset($_POST['add_series'])) {
        $title = $_POST['series_title'];
        $genre_id = $_POST['series_genre'];
        $description = $_POST['series_description'];
        $release_year = $_POST['series_release_year'];

        $image_path = handle_upload('series_image', '../assets/images/');
        $thumbnail_path = handle_upload('series_thumbnail', '../assets/thumbnail/');

        if ($image_path) {
            $created_by = $_SESSION['user']['id'];
            $stmt_series = $conn->prepare("INSERT INTO series (title, description, release_year, image_path, thumbnail_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_series->bind_param("ssissi", $title, $description, $release_year, $image_path, $thumbnail_path, $created_by);

            if ($stmt_series->execute()) {
                $last_series_id = $conn->insert_id;
                $stmt_genre = $conn->prepare("INSERT INTO series_genres (series_id, genre_id) VALUES (?, ?)");
                $stmt_genre->bind_param("ii", $last_series_id, $genre_id);
                $stmt_genre->execute();
                $message = "New series added successfully!";
            } else {
                $message = "Error adding series.";
            }
        } else {
            $message = "Error uploading image or video file.";
        }
    }
    
    if (isset($_POST['save_episode'])) {
        $series_id = $_POST['episode_series'];
        $episode_number = $_POST['episode_number'];
        $title = $_POST['episode_title'];
        $episode_id = $_POST['episode_id'];

        $video_path = handle_upload('episode_video', '../assets/videos/');

        if (!empty($episode_id)) { // UPDATE logic  
            $sql = "UPDATE episodes SET series_id = ?, episode_number = ?, title = ?";
            $params_types = "iis";
            $params_values = [$series_id, $episode_number, $title];

            if ($video_path) {
                $sql .= ", video_path = ?";
                $params_types .= "s";
                $params_values[] = $video_path;
            }

            $sql .= " WHERE id = ?";
            $params_types .= "i";
            $params_values[] = $episode_id;
            
            $stmt_episode = $conn->prepare($sql);
            $stmt_episode->bind_param($params_types, ...$params_values);

            if ($stmt_episode->execute()) {
                $message = "Episode updated successfully!";
            } else {
                $message = "Error updating episode.";
            }

        } else { // INSERT logic
            $stmt_check = $conn->prepare("SELECT id FROM episodes WHERE series_id = ? AND episode_number = ?");
            $stmt_check->bind_param("ii", $series_id, $episode_number);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $message = "Error: Episode number already exists for this series.";
            } elseif ($video_path) {
                $stmt_episode = $conn->prepare("INSERT INTO episodes (series_id, episode_number, title, video_path) VALUES (?, ?, ?, ?)");
                $stmt_episode->bind_param("iiss", $series_id, $episode_number, $title, $video_path);
                if ($stmt_episode->execute()) {
                    $message = "New episode added successfully!";
                } else {
                    $message = "Error adding episode.";
                }
            } else {
                $message = "Error: A video file is required when adding a new episode.";
            }
        }
    }
}

if (isset($_GET['get_details'])) {
    header('Content-Type: application/json');
    $id = $_GET['id'];
    $type = $_GET['type'];
    if ($type == 'movie') {
        $stmt = $conn->prepare("SELECT m.*, mg.genre_id FROM movies m LEFT JOIN movie_genres mg ON m.id = mg.movie_id WHERE m.id = ?");
    } elseif ($type == 'series') {
        $stmt = $conn->prepare("SELECT s.*, sg.genre_id FROM series s LEFT JOIN series_genres sg ON s.id = sg.series_id WHERE s.id = ?");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
    exit;
}

if (isset($_GET['get_episode_details'])) {
    header('Content-Type: application/json');
    $series_id = intval($_GET['series_id']);
    $episode_number = intval($_GET['episode_number']);

    $stmt = $conn->prepare("SELECT * FROM episodes WHERE series_id = ? AND episode_number = ?");
    $stmt->bind_param("ii", $series_id, $episode_number);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
    exit;
}

if (isset($_GET['delete_movie'])) {
    $id = intval($_GET['delete_movie']);
    $stmt1 = $conn->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    
    header("Location: manage_movies.php");
    exit;
}

if (isset($_GET['delete_series'])) {
    $id = intval($_GET['delete_series']);
    $stmt1 = $conn->prepare("DELETE FROM series_genres WHERE series_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM series WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    header("Location: manage_series.php");
    exit;
}

if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_users.php");
    exit;
}


$total_movies = $conn->query("SELECT COUNT(id) as count FROM movies")->fetch_assoc()['count'];
$total_series = $conn->query("SELECT COUNT(id) as count FROM series")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$unread_messages = $conn->query("SELECT COUNT(id) as count FROM contact_messages WHERE is_read = 0")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include "../includes/header.php"; ?>

    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
            <nav class="admin-nav">
                <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_movies.php"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="manage_series.php"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_messages.php"><i class="fas fa-envelope"></i> Manage Messages</a>
            </nav>
        </aside>

        <main class="admin-main-content">
            <div class="dashboard-header"><h1>Dashboard</h1></div>
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
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
                <div class="stat-card">
                    <a href="manage_messages.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:20px;">
                        <div class="stat-card-icon"><i class="fas fa-envelope-open-text"></i></div>
                        <div class="stat-card-info">
                            <h3><?php echo $unread_messages; ?></h3><p>Unread Messages</p>
                        </div>
                    </a>
                </div>
            </section>
        </main>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>
</html>
