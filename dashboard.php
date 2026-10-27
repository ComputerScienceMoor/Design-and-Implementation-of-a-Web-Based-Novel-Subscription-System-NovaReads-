<?php

session_start();
include("connect.php");
include("subscription_check.php");
/* ===========================
   LOGIN CHECK
=========================== */

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* ===========================
   GET USER
=========================== */

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

$user_result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($user_result) == 0){

    session_destroy();

    header("Location: login.php");

    exit();
}

$user = mysqli_fetch_assoc($user_result);

/* ===========================
   DASHBOARD STATISTICS
=========================== */

$totalNovels = 0;
$totalFavorites = 0;

/* Total Novels */

$query = $conn ? mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM novels"
) : false;

if($query){

    $row = mysqli_fetch_assoc($query);

    $totalNovels = $row['total'];
}

/* Total Favorites */

$query = mysqli_query(

    $conn,

    "SELECT COUNT(*) AS total

     FROM favorites

     WHERE user_email='"

     . mysqli_real_escape_string($conn,$email)

     . "'"

);

if($query){

    $row = mysqli_fetch_assoc($query);

    $totalFavorites = $row['total'];
}

/* ===========================
   SEARCH
=========================== */

$search = "";

if(isset($_GET['search'])){

    $search = trim($_GET['search']);
}

if($search != ""){

    $keyword = "%".$search."%";

    if ($conn) {

    $stmt = mysqli_prepare(

        $conn,

        "SELECT *

         FROM novels

         WHERE

         title LIKE ?

         OR

         author LIKE ?

         OR

         genre LIKE ?

         ORDER BY id DESC"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "sss",

        $keyword,

        $keyword,

        $keyword

    );

    mysqli_stmt_execute($stmt);

    $novels = mysqli_stmt_get_result($stmt);

    }

}else if ($conn) {

    $novels = mysqli_query(

        $conn,

        "SELECT *

         FROM novels

         ORDER BY id DESC"

    );

}else{

    $novels = false;

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Dashboard

</title>

<link
rel="stylesheet"
href="novel.css">

</head>

<body>

<?php include("toast.php"); ?>

<header>

<div class="top-bar">

<p>

Logged in as

<strong>

<?php echo htmlspecialchars($user['fullname']); ?>

</strong>

</p>

<p>

Plan:

<strong>

<?php echo htmlspecialchars($user['subscription']); ?>

</strong>

</p>

</div>

<nav>

<h1 class="logo">

NovaReads

</h1>

<div class="nav-links">

<a href="dashboard.php">

Home

</a>

<a href="favorites.php">

Favorites

</a>

<a href="continue_reading.php">

Continue Reading

</a>

<a href="subscribe.php">

Subscription

</a>

<a href="profile.php">

Profile

</a>

<?php if($user['role']=="admin"){ ?>

<a href="admin.php">

Admin Panel

</a>

<?php } ?>

<a href="logout.php">

Logout

</a>

</div>

</nav>

</header>
<!-- SEARCH SECTION -->

<div class="search-box">

<form method="GET" action="dashboard.php">

<input
type="text"
name="search"
placeholder="Search by title, author or genre..."
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">

Search

</button>

</form>

</div>

<!-- HERO SECTION -->

<div class="hero-dashboard">

<div class="hero-overlay">

<h1>

Welcome Back,
<?php echo htmlspecialchars($user['fullname']); ?>

</h1>

<p>

Discover new worlds, continue your adventures,
and unlock premium stories.

</p>

<a href="#novels">

<button class="hero-btn">

Browse Novels

</button>

</a>

</div>

</div>

<!-- DASHBOARD STATS -->

<div class="stats-container">

<div class="stat-card">

<h2>

<?php echo $totalNovels; ?>

</h2>

<p>

Total Novels

</p>

</div>

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

<?php echo htmlspecialchars($user['subscription']); ?>

</h2>

<p>

Current Plan

</p>

</div>

</div>

<!-- NOVELS SECTION -->

<section
id="novels"
class="dashboard">

<h2 class="section-title">

Available Novels

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

<strong>Genre:</strong>

<?php echo htmlspecialchars($novel['genre']); ?>

</p>

<p>

<strong>Author:</strong>

<?php echo htmlspecialchars($novel['author']); ?>

</p>

<p>

<strong>Access:</strong>

<?php echo htmlspecialchars($novel['access_type']); ?>

</p>
<!-- ACTION BUTTONS -->

<div class="card-buttons">

<a href="add_favorite.php?id=<?php echo (int)$novel['id']; ?>">

<button class="favorite-btn">

❤️ Add to Favorites

</button>

</a>

<?php if($novel['access_type'] == "Premium"){ ?>

<?php
if(
$user['subscription'] == "Premium"
||
$user['subscription'] == "VIP"
){
?>

<a href="novel.php?id=<?php echo (int)$novel['id']; ?>">

<button class="hero-btn">

📖 Read Premium Novel

</button>

</a>

<?php } else { ?>

<a href="subscribe.php">

<button class="premium-btn">

🔒 Premium Only

</button>

</a>

<?php } ?>

<?php } else { ?>

<a href="novel.php?id=<?php echo (int)$novel['id']; ?>">

<button class="hero-btn">

📖 Read Now

</button>

</a>

<?php } ?>

</div>

</div>

<?php

}

}else{

?>

<div class="empty-state">

<h2>

📚 No Novels Found

</h2>

<p>

No novels matched your search.

</p>

<a href="dashboard.php">

<button>

View All Novels

</button>

</a>

</div>

<?php

}

?>

</div>

</section>
<!-- FOOTER -->

<footer class="footer">

<div class="footer-content">

<p>

&copy; <?php echo date("Y"); ?>

NovaReads. All Rights Reserved.

</p>



</div>

</footer>

</body>

</html>