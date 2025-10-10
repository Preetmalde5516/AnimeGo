<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../includes/db_connect.php';

// 1. Check if the user is logged in
if (!isset($_SESSION['user']['id'])) {
    // Redirect to login or home page if not logged in
    header("Location: ../public/index.php");
    exit();
}

// 2. Check if the required POST data was sent
if (!isset($_POST['content_id']) || !isset($_POST['content_type'])) {
    // Redirect back if the form data is incomplete
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../public/index.php'));
    exit();
}

$user_id = $_SESSION['user']['id'];
$content_id = intval($_POST['content_id']);
$content_type = $_POST['content_type'];

// 3. Validate the content type
if ($content_type !== 'movie' && $content_type !== 'series') {
    // Redirect back if the content type is invalid
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../public/index.php'));
    exit();
}

// 4. Check if the item is already in the user's watchlist
$stmt_check = $conn->prepare("SELECT id FROM user_watchlist WHERE user_id = ? AND content_id = ? AND content_type = ?");
$stmt_check->bind_param("iis", $user_id, $content_id, $content_type);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // 5a. If it exists, REMOVE it
    $stmt_delete = $conn->prepare("DELETE FROM user_watchlist WHERE user_id = ? AND content_id = ? AND content_type = ?");
    $stmt_delete->bind_param("iis", $user_id, $content_id, $content_type);
    $stmt_delete->execute();
    $stmt_delete->close();
} else {
    // 5b. If it does not exist, ADD it
    $stmt_insert = $conn->prepare("INSERT INTO user_watchlist (user_id, content_id, content_type) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $user_id, $content_id, $content_type);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

// 6. Redirect the user back to the previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>