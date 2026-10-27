<?php

session_start();
include("connect.php");

if (!isset($_SESSION['email'])) {
    $_SESSION['message'] = "Please login first.";
    $_SESSION['message_type'] = "error";
    header("Location: login.php");
    exit();
}

if (($_GET['plan'] ?? "") !== "Free") {
    $_SESSION['message'] = "Invalid subscription request.";
    $_SESSION['message_type'] = "error";
    header("Location: subscribe.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET subscription = 'Free',
         transaction_id = NULL,
         subscription_date = NULL,
         subscription_expiry = NULL
     WHERE email = ?"
);
mysqli_stmt_bind_param($stmt, "s", $email);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$_SESSION['message'] = $success
    ? "Your subscription has been changed to Free."
    : "Unable to change your subscription.";
$_SESSION['message_type'] = $success ? "success" : "error";

header("Location: subscribe.php");
exit();

?>
