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
    <title>Manage Users - Admin</title>
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
                <a href="manage_series.php"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php" class="active"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_messages.php"><i class="fas fa-envelope"></i> Manage Messages</a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <div class="dashboard-header"><h1>Manage Users</h1></div>
            <section class="content-table-section">
                <h2>All Users</h2>
                <table class="content-table">
                    <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                        $sql_users = "SELECT id, username, email, created_at FROM users ORDER BY id DESC";
                        $user_result = $conn->query($sql_users);
                        if ($user_result && $user_result->num_rows > 0) {
                            while ($row = $user_result->fetch_assoc()) :
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td class="actions">
                                <a href="admin.php?delete_user=<?php echo $row['id']; ?>" class="delete" title="Delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile;
                        } else {
                            echo "<tr><td colspan='5'>No users found.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>