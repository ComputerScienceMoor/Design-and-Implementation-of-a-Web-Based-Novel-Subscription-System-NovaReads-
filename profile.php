<?php

session_start();
include("connect.php");
include("subscription_check.php");
/* ==========================
   LOGIN CHECK
========================== */

if(!isset($_SESSION['email'])){

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* ==========================
   GET USER
========================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$userResult = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($userResult);

if(!$user){

    session_destroy();

    header("Location: login.php");
    exit();
}

/* ==========================
   FAVORITES COUNT
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT COUNT(*) AS total
     FROM favorites
     WHERE user_email=?"

);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$favoriteResult = mysqli_stmt_get_result($stmt);

$totalFavorites =
mysqli_fetch_assoc($favoriteResult)['total'];

/* ==========================
   READING COUNT
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT COUNT(*) AS total
     FROM reading_progress
     WHERE user_email=?"

);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$readingResult = mysqli_stmt_get_result($stmt);

$totalReading =
mysqli_fetch_assoc($readingResult)['total'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

My Profile

</title>

<link
rel="stylesheet"
href="novel.css">

</head>

<body>

<?php include("toast.php"); ?>

<nav>

<h1 class="logo">

NovaReads

</h1>

<div class="nav-links">

<a href="dashboard.php">

Dashboard

</a>

<a href="favorites.php">

Favorites

</a>

<a href="continue_reading.php">

Continue Reading

</a>

<a href="logout.php">

Logout

</a>

</div>

</nav>

<div class="hero-dashboard">

<div class="hero-overlay">

<h1>

Welcome,

<?php echo htmlspecialchars($user['fullname']); ?>

</h1>

<p>

Manage your NovaReads account.

</p>

</div>

</div>

<section class="dashboard">

<div class="profile-card">
    <h2>

👤 My Profile

</h2>

<hr>

<br>

<p>

<strong>Full Name</strong>

<br>

<?php echo htmlspecialchars($user['fullname']); ?>

</p>

<br>

<p>

<strong>Email</strong>

<br>

<?php echo htmlspecialchars($user['email']); ?>

</p>

<br>

<p>

<strong>Role</strong>

<br>

<?php echo ucfirst(htmlspecialchars($user['role'])); ?>

</p>

<br>

<p>

<strong>Subscription</strong>

<br>

<?php

echo empty($user['subscription'])

? "Free"

: htmlspecialchars($user['subscription']);

?>

</p>

<br>

<div class="stats-container">

<div class="stat-card">

<h2>

<?php echo $totalFavorites; ?>

</h2>

<p>

Favorites

</p>

</div>

<div class="stat-card">

<h2>

<?php echo $totalReading; ?>

</h2>

<p>

Reading Progress

</p>

</div>

</div>

<br>

<div class="profile-buttons">

<a href="edit_profile.php">

<button>

✏ Edit Profile

</button>

</a>

<a href="subscribe.php">

<button>

💳 Subscription

</button>

</a>

<a href="favorites.php">

<button>

❤️ Favorites

</button>

</a>

<?php

if($user['role']=="admin"){

?>

<a href="admin.php">

<button>

🛠 Admin Panel

</button>

</a>

<?php

}

?>

</div>

</div>

</section>

<footer class="footer">

<div class="footer-content">

<p>

&copy; <?php echo date("Y"); ?>

NovaReads

</p>

</div>

</footer>

</body>

</html>
