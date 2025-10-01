<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    include 'header.php';
    include 'db_connect.php';

    // // Redirect to login page if user is not logged in
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: login.php");
    //     exit();
    // }

    // $user_id = $_SESSION['user_id'];

    // Fetch all movies from the current user's watchlist
    $stmt = $conn->prepare("
        SELECT m.* FROM movies m
        JOIN watchlist w ON m.id = w.movie_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .page-header {
            padding: 100px 0 40px 0;
            text-align: center;
            background: linear-gradient(rgba(18, 18, 18, 0.8), rgba(18, 18, 18, 1));
        }
        .page-title {
            color: #ff6b6b;
            font-size: 2.5rem;
        }
        .empty-list-message {
            text-align: center;
            color: #ccc;
            padding: 50px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <main>
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">My Watchlist</h1>
            </div>
        </div>
        
        <section class="section">
            <div class="container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="cards">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="movie-card" style="background-image: url('assets/images/<?php echo htmlspecialchars($row['image_path']); ?>');">
                                <div class="card-content">
                                    <div class="info-section">
                                        <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    </div>
                                    <a href="anime_info.php?id=<?php echo $row['id']; ?>" class="card-link"></a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-list-message">Your watchlist is empty. Add some anime to see them here!</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>

<?php
    $stmt->close();
    $conn->close();
?>