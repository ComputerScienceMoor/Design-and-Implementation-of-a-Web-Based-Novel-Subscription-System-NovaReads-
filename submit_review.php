<?php

session_start();
include("connect.php");

if (!isset($_SESSION['email'])) {
    $_SESSION['message'] = "Please login first.";
    $_SESSION['message_type'] = "error";
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$novel_id = isset($_POST['novel_id']) ? (int) $_POST['novel_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$review = trim($_POST['review'] ?? "");

if ($novel_id <= 0 || $rating < 1 || $rating > 5 || $review === "") {
    $_SESSION['message'] = "Please provide a valid rating and review.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id FROM novels WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$novel_exists = mysqli_num_rows($result) > 0;
mysqli_stmt_close($stmt);

if (!$novel_exists) {
    $_SESSION['message'] = "Novel not found.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO reviews (novel_id, user_email, rating, review)
     VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "isis", $novel_id, $email, $rating, $review);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$_SESSION['message'] = $success
    ? "Your review was submitted."
    : "Unable to submit your review.";
$_SESSION['message_type'] = $success ? "success" : "error";

header("Location: rewiews.php?novel_id=" . $novel_id);
exit();

?>
