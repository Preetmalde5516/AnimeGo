<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Enforce admin-only access
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: ../public/index.php");
    exit;
}
include "../includes/db_connect.php";

// Check for a message from a previous action and display it
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message so it doesn't appear again
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Admin</title>
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
                <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_movies.php"><i class="fas fa-film"></i> Manage Movies</a>
                <a href="manage_series.php"><i class="fas fa-tv"></i> Manage Series</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_messages.php" class="active"><i class="fas fa-envelope"></i> Manage Messages</a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <div class="dashboard-header"><h1>Manage Messages</h1></div>
            
            <?php if (!empty($message)): ?>
                <div class="message success" style="display:block;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <section class="content-table-section">
                <h2>All Messages</h2>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_messages = "SELECT id, name, email, subject, message, created_at, is_read FROM contact_messages ORDER BY created_at DESC";
                        $message_result = $conn->query($sql_messages);
                        if ($message_result && $message_result->num_rows > 0) {
                            while ($row = $message_result->fetch_assoc()) :
                        ?>
                        <tr style="<?php echo $row['is_read'] ? '' : 'font-weight: bold; background-color: #3a3a3a;'; ?>">
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                            <td><?php echo date("M d, Y, h:i A", strtotime($row['created_at'])); ?></td>
                            <td class="actions">
                                <?php if (!$row['is_read']): ?>
                                <a href="admin.php?mark_read=<?php echo $row['id']; ?>" title="Mark as Read"><i class="fas fa-check-circle"></i></a>
                                <?php endif; ?>
                                <a href="admin.php?delete_message=<?php echo $row['id']; ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this message?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile;
                        } else {
                            echo "<tr><td colspan='6'>No messages found.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
