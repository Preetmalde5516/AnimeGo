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
    <title>Manage Series - Admin</title>
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
                <a href="manage_movies.php"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="manage_series.php" class="active"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <div class="dashboard-header"><h1>Manage Series</h1></div>
            <section class="content-table-section">
                <div class="section-header">
                    <h2>All Series</h2>
                    <div>
                    <button id="addSeriesBtn" class="btn-add-new"><i class="fas fa-plus"></i> Add New Series</button>
                    <button id="addEpisodeBtn" class="btn-add-new" style="margin-left: 10px;"><i class="fas fa-upload"></i> Upload Episode</button>
                    </div>
                </div>
                <table class="content-table">
                    <thead><tr><th>Title</th><th>Genre</th><th>Release Year</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                        $sql_series = "SELECT s.id, s.title, s.release_year, GROUP_CONCAT(g.name SEPARATOR ', ') as genres FROM series s LEFT JOIN series_genres sg ON s.id = sg.series_id LEFT JOIN genres g ON sg.genre_id = g.id GROUP BY s.id ORDER BY s.id DESC";
                        $series_result = $conn->query($sql_series);
                        if ($series_result && $series_result->num_rows > 0) {
                            while ($row = $series_result->fetch_assoc()) :
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['genres']); ?></td>
                            <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                            <td class="actions">
                                <a href="#" class="edit-series" data-id="<?php echo $row['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="admin.php?delete_series=<?php echo $row['id']; ?>" class="delete" title="Delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile;
                        } else {
                            echo "<tr><td colspan='4'>No series found.</td></tr>";
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