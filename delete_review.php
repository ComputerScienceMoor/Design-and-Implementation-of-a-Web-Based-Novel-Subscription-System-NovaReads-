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
$review_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($review_id <= 0) {
    $_SESSION['message'] = "Invalid review.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT novel_id FROM reviews WHERE id = ? AND user_email = ?"
);
mysqli_stmt_bind_param($stmt, "is", $review_id, $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$review = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$review) {
    $_SESSION['message'] = "Review not found or access denied.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}

$novel_id = (int) $review['novel_id'];
$stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ? AND user_email = ?");
mysqli_stmt_bind_param($stmt, "is", $review_id, $email);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$_SESSION['message'] = $success
    ? "Review deleted."
    : "Unable to delete your review.";
$_SESSION['message_type'] = $success ? "success" : "error";

header("Location: rewiews.php?novel_id=" . $novel_id);
exit();

?>
