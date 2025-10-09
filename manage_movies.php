<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: index.php");
    exit;
}
include "db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies - Admin</title>
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
                <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_movies.php" class="active"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="manage_series.php"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_messages.php"><i class="fas fa-envelope"></i> Manage Messages</a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <div class="dashboard-header">
                <h1>Manage Movies</h1>
            </div>
            <section class="content-table-section">
                <div class="section-header">
                    <h2>All Movies</h2>
                    <button id="addMovieBtn" class="btn-add-new"><i class="fas fa-plus"></i> Add New Movie</button>
                </div>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Release Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_movies = "SELECT m.id, m.title, m.release_year, GROUP_CONCAT(g.name SEPARATOR ', ') as genres FROM movies m LEFT JOIN movie_genres mg ON m.id = mg.movie_id LEFT JOIN genres g ON mg.genre_id = g.id GROUP BY m.id ORDER BY m.id DESC";
                        $movie_result = $conn->query($sql_movies);
                        if ($movie_result && $movie_result->num_rows > 0) {
                            while ($row = $movie_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['genres']); ?></td>
                                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                                    <td class="actions">
                                        <a href="#" class="edit-movie" data-id="<?php echo $row['id']; ?>" title="Edit"><i
                                                class="fas fa-edit"></i></a>
                                        <a href="admin.php?delete_movie=<?php echo $row['id']; ?>" class="delete" title="Delete"
                                            onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } else {
                            echo "<tr><td colspan='4'>No movies found.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <?php include "modals.php"; ?>
    <?php include "footer.php"; ?>
    <script src="admin.js"></script>
</body>

</html>