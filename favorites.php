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
   SEARCH
========================== */

$search = "";

if(isset($_GET['search'])){

    $search = trim($_GET['search']);
}

/* ==========================
   GET FAVORITES
========================== */

if($search != ""){

    $keyword = "%".$search."%";

    $stmt = mysqli_prepare(

        $conn,

        "SELECT novels.*

         FROM favorites

         INNER JOIN novels

         ON favorites.novel_id = novels.id

         WHERE

         favorites.user_email=?

         AND

         (

            novels.title LIKE ?

            OR novels.author LIKE ?

            OR novels.genre LIKE ?

         )

         ORDER BY novels.title ASC"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "ssss",

        $email,

        $keyword,

        $keyword,

        $keyword

    );

}else{

    $stmt = mysqli_prepare(

        $conn,

        "SELECT novels.*

         FROM favorites

         INNER JOIN novels

         ON favorites.novel_id = novels.id

         WHERE favorites.user_email=?

         ORDER BY novels.title ASC"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "s",

        $email

    );

}

mysqli_stmt_execute($stmt);

$favorites = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

My Favorites

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

<a href="continue_reading.php">

Continue Reading

</a>

<a href="profile.php">

Profile

</a>

<a href="logout.php">

Logout

</a>

</div>

</nav>

<div class="hero-dashboard">

<div class="hero-overlay">

<h1>

❤️ My Favorite Novels

</h1>

<p>

All your saved stories in one place.

</p>

</div>

</div>

<div class="search-box">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search favorites..."
value="<?php echo htmlspecialchars($search); ?>">

<button>

Search

</button>

</form>

</div>

<section class="dashboard">

<h2 class="section-title">

Your Favorites

</h2>

<div class="novel-container">
    <?php

if(mysqli_num_rows($favorites)>0){

while($novel=mysqli_fetch_assoc($favorites)){

?>

<div class="novel-card">

<img
src="images/<?php echo htmlspecialchars($novel['image']); ?>"
alt="<?php echo htmlspecialchars($novel['title']); ?>">

<h3>

<?php echo htmlspecialchars($novel['title']); ?>

</h3>

<p>

<strong>Author:</strong>

<?php echo htmlspecialchars($novel['author']); ?>

</p>

<p>

<strong>Genre:</strong>

<?php echo htmlspecialchars($novel['genre']); ?>

</p>

<p>

<strong>Access:</strong>

<?php echo htmlspecialchars($novel['access_type']); ?>

</p>

<div class="card-buttons">

<a href="novel.php?id=<?php echo (int)$novel['id']; ?>">

<button>

📖 View Novel

</button>

</a>

<a href="remove_favorite.php?id=<?php echo (int)$novel['id']; ?>">

<button class="danger-btn">

🗑 Remove

</button>

</a>

</div>

</div>

<?php

}

}else{

?>

<div class="empty-state">

<h2>

No Favorites Yet

</h2>

<p>

Start adding novels to your favorites.

</p>

<br>

<a href="dashboard.php">

<button>

Browse Novels

</button>

</a>

</div>

<?php

}

?>

</div>

</section>

<footer class="footer">

<p>

&copy; <?php echo date("Y"); ?>

NovaReads

</p>

</footer>

</body>

</html>