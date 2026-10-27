<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful</title>
<link rel="stylesheet" href="novel.css">
<script src="novel.js" defer></script>

</head>

<body>

<?php include("toast.php"); ?>

<div class="auth-page">

<div class="form-container" style="text-align:center;">

<h1>🎉 Payment Successful!</h1>

<p>Your subscription has been activated. Enjoy NovaReads!</p>

<br>

<a href="dashboard.php">
    <button>Go to Dashboard</button>
</a>

</div>

</div>

</body>
