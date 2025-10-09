<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: index.php");
    exit;
}

include "db_connect.php";

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
        
        $image_path = handle_upload('movie_image', 'assets/images/');
        $thumbnail_path = handle_upload('movie_thumbnail', 'assets/thumbnail/');
        $video_path = handle_upload('movie_video', 'assets/videos/');

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <?php include "footer.php"; ?>
</body>
</html>