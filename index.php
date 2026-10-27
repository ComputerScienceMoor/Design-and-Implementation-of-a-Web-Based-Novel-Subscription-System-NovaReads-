<?php
session_start();
include("connect.php");

/* Get Latest Novels */

$novels = false;

if ($conn) {

    $novels = mysqli_query(
        $conn,
        "SELECT * FROM novels ORDER BY id DESC LIMIT 6"
    );

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>NovaReads</title>

<link rel="stylesheet" href="novel.css">

</head>

<body>

<!-- Navigation -->

<nav>

<h1 class="logo">
NovaReads
</h1>

<div class="nav-links">

<?php if(isset($_SESSION['email'])) { ?>

<a href="dashboard.php">
Dashboard
</a>

<a href="logout.php">
Logout
</a>

<?php } else { ?>

<a href="login.php">
Login
</a>

<a href="register.php">
Register
</a>

<?php } ?>

</div>

</nav>

<!-- Hero Section -->

<section class="hero">

<div class="hero-content">

<h1>

Read Unlimited Stories Anytime

</h1>

<p>

Discover amazing novels from talented authors.
Read free stories or upgrade to Premium for exclusive content.

</p>

<?php if(isset($_SESSION['email'])) { ?>

<a href="dashboard.php">

<button class="hero-btn">

Go to Dashboard

</button>

</a>

<?php } else { ?>

<a href="register.php">

<button class="hero-btn">

Get Started

</button>

</a>

<?php } ?>

</div>

</section>

<!-- Features -->

<section class="features">

<h2>
Why Choose NovaReads?
</h2>

<div class="feature-container">

<div class="feature-card">

<h3>
📚 Thousands of Stories
</h3>

<p>

Enjoy novels from different genres.

</p>

</div>

<div class="feature-card">

<h3>
⭐ Save Favorites
</h3>

<p>

Bookmark novels you love.

</p>

</div>

<div class="feature-card">

<h3>
📖 Continue Reading
</h3>

<p>

Resume exactly where you stopped.

</p>

</div>

<div class="feature-card">

<h3>
💎 Premium Access
</h3>

<p>

Unlock exclusive chapters.

</p>

</div>

</div>

</section>

<!-- Latest Novels -->

<section class="dashboard">

<h2 class="section-title">

Latest Novels

</h2>

<div class="novel-container">

<?php

if ($novels && mysqli_num_rows($novels) > 0) {

while($novel = mysqli_fetch_assoc($novels)){

$imageName = !empty($novel['image']) && file_exists("images/" . $novel['image'])
    ? $novel['image']
    : "placeholder.svg";

?>

<div class="novel-card">

<img
src="images/<?php echo htmlspecialchars($imageName); ?>"
alt="<?php echo htmlspecialchars($novel['title']); ?>">

<h3>

<?php echo htmlspecialchars($novel['title']); ?>

</h3>

<p>

Genre:
<?php echo htmlspecialchars($novel['genre']); ?>

</p>

<p>

Author:
<?php echo htmlspecialchars($novel['author']); ?>

</p>

<p>

<?php echo htmlspecialchars($novel['access_type']); ?>

</p>

<?php if(isset($_SESSION['email'])) { ?>

<a href="novel.php?id=<?php echo (int)$novel['id']; ?>">

<button>

Read Now

</button>

</a>

<?php } else { ?>

<a href="login.php">

<button>

Login to Read

</button>

</a>

<?php } ?>

</div>

<?php

}

}else{

?>

<p style="text-align:center;width:100%;">

No novels available yet.

</p>

<?php } ?>

</div>

</section>

<!-- Footer -->

<footer>

<p>

© <?php echo date("Y"); ?> NovaReads.
All Rights Reserved.

</p>

</footer>

</body>

</html>