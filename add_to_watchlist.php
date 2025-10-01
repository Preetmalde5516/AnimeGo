<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db_connect.php';

// Set the content type to JSON for the response
header('Content-Type: application/json');

// 1. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to modify your watchlist.']);
    exit();
}

// 2. Check if a movie ID was sent with the request
if (!isset($_POST['movie_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request. No movie specified.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$movie_id = intval($_POST['movie_id']);

// 3. Check if the item is already in the user's watchlist
$stmt_check = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
$stmt_check->bind_param("ii", $user_id, $movie_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // 4a. If it exists, REMOVE it
    $stmt_delete = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $stmt_delete->bind_param("ii", $user_id, $movie_id);
    if ($stmt_delete->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove from watchlist.']);
    }
    $stmt_delete->close();
} else {
    // 4b. If it does not exist, ADD it
    $stmt_insert = $conn->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $movie_id);
    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'added']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to watchlist.']);
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>