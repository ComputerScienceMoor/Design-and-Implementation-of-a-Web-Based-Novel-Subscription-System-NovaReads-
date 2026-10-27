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
   GET READING HISTORY
========================== */

if($search != ""){

    $keyword = "%".$search."%";

    $stmt = mysqli_prepare(

        $conn,

        "SELECT
            reading_progress.*,
            novels.title,
            novels.image,
            chapters.chapter_title

        FROM reading_progress

        INNER JOIN novels
        ON reading_progress.novel_id = novels.id

        INNER JOIN chapters
        ON reading_progress.chapter_id = chapters.id

        WHERE

        reading_progress.user_email=?

        AND

        (
            novels.title LIKE ?
            OR
            chapters.chapter_title LIKE ?
        )

        ORDER BY reading_progress.last_read DESC"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "sss",

        $email,

        $keyword,

        $keyword

    );

}else{

    $stmt = mysqli_prepare(

        $conn,

        "SELECT
            reading_progress.*,
            novels.title,
            novels.image,
            chapters.chapter_title

        FROM reading_progress

        INNER JOIN novels
        ON reading_progress.novel_id = novels.id

        INNER JOIN chapters
        ON reading_progress.chapter_id = chapters.id

        WHERE

        reading_progress.user_email=?

        ORDER BY reading_progress.last_read DESC"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "s",

        $email

    );

}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Continue Reading

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

📖 Continue Reading

</h1>

<p>

Continue exactly where you stopped reading.

</p>

</div>

</div>

<div class="search-box">

<form method="GET">

<input

type="text"

name="search"

placeholder="Search reading history..."

value="<?php echo htmlspecialchars($search); ?>">

<button>

Search

</button>

</form>

</div>

<section class="dashboard">

<h2 style="text-align:center;">

Your Reading History

</h2>

<div class="novel-container">
    <?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<div class="novel-card">

<img

src="images/<?php echo htmlspecialchars($row['image']); ?>"

alt="<?php echo htmlspecialchars($row['title']); ?>">

<h3>

<?php echo htmlspecialchars($row['title']); ?>

</h3>

<p>

<strong>Last Chapter</strong>

</p>

<p>

<?php echo htmlspecialchars($row['chapter_title']); ?>

</p>

<p>

<strong>Last Read</strong>

</p>

<p>

<?php

echo date(

"F j, Y g:i A",

strtotime($row['last_read'])

);

?>

</p>

<div class="card-buttons">

<a href="read.php?chapter=<?php echo (int)$row['chapter_id']; ?>">

<button class="hero-btn">

▶ Continue Reading

</button>

</a>

<a href="novel.php?id=<?php echo (int)$row['novel_id']; ?>">

<button>

View Novel

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

📚 No Reading History

</h2>

<p>

You haven't started reading any novels yet.

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

<div class="footer-content">

<p>

&copy; <?php echo date("Y"); ?>

NovaReads

</p>

<p>

Keep turning pages 📚

</p>

</div>

</footer>

</body>

</html>