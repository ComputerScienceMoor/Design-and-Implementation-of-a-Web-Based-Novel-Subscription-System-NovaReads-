<?php

session_start();

include("connect.php");
include("subscription_check.php");


/* ==========================
   LOGIN CHECK
========================== */

if (!isset($_SESSION['email'])) {

    header("Location: login.php");

    exit();

}


$email = $_SESSION['email'];


/* ==========================
   GET USER
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM users

     WHERE email = ?"

);


mysqli_stmt_bind_param(

    $stmt,

    "s",

    $email

);


mysqli_stmt_execute($stmt);


$userResult = mysqli_stmt_get_result($stmt);


$user = mysqli_fetch_assoc($userResult);


mysqli_stmt_close($stmt);


if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit();

}


/* ==========================
   CHECK NOVEL ID
========================== */

if (

    !isset($_GET['id']) ||

    (int) $_GET['id'] <= 0

) {

    header("Location: dashboard.php");

    exit();

}


$novel_id = (int) $_GET['id'];


/* ==========================
   GET NOVEL
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM novels

     WHERE id = ?"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);


mysqli_stmt_execute($stmt);


$novelResult = mysqli_stmt_get_result($stmt);


if (

    mysqli_num_rows($novelResult) === 0

) {


    $_SESSION['message'] =

        "Novel not found.";


    $_SESSION['message_type'] =

        "error";


    header("Location: dashboard.php");

    exit();

}


$novel = mysqli_fetch_assoc($novelResult);


mysqli_stmt_close($stmt);


/* ==========================
   GET CHAPTERS
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM chapters

     WHERE novel_id = ?

     ORDER BY id ASC"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);


mysqli_stmt_execute($stmt);


$chapters = mysqli_stmt_get_result($stmt);


/* ==========================
   GET REVIEWS
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM reviews

     WHERE novel_id = ?

     ORDER BY created_at DESC"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);


mysqli_stmt_execute($stmt);


$reviews = mysqli_stmt_get_result($stmt);


/* ==========================
   AVERAGE RATING
========================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT

        AVG(rating) AS average_rating,

        COUNT(*) AS total_reviews

     FROM reviews

     WHERE novel_id = ?"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);


mysqli_stmt_execute($stmt);


$ratingResult = mysqli_stmt_get_result($stmt);


$rating = mysqli_fetch_assoc($ratingResult);


$averageRating = $rating['average_rating']

    ? number_format(

        $rating['average_rating'],

        1

    )

    : "0.0";


$totalReviews = (int) $rating['total_reviews'];


/* ==========================
   CONTINUE READING
========================== */

$email_safe = mysqli_real_escape_string(

    $conn,

    $email

);


$continue = mysqli_query(

    $conn,

    "SELECT chapter_id

     FROM reading_progress

     WHERE user_email = '$email_safe'

     AND novel_id = '$novel_id'

     LIMIT 1"

);


$continueChapter = null;


if (

    mysqli_num_rows($continue) > 0

) {


    $continueChapter =

        mysqli_fetch_assoc($continue);

}

?>


<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">


<meta

    name="viewport"

    content="width=device-width, initial-scale=1.0"

>


<title>

<?php echo htmlspecialchars(

    $novel['title']

); ?>

| NovaReads

</title>


<link

    rel="stylesheet"

    href="novel.css"

>


</head>


<body>


<?php include("toast.php"); ?>


<!-- ==========================
     NAVIGATION
========================== -->


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


<a href="profile.php">

Profile

</a>


<a href="logout.php">

Logout

</a>


</div>


</nav>


<!-- ==========================
     HERO
========================== -->


<div class="hero-dashboard">


<div class="hero-overlay">


<h1>

<?php echo htmlspecialchars(

    $novel['title']

); ?>

</h1>


<p>


Written by


<strong>

<?php echo htmlspecialchars(

    $novel['author']

); ?>

</strong>


</p>


</div>


</div>


<!-- ==========================
     NOVEL DETAILS
========================== -->


<section class="dashboard">


<div class="novel-details">


<div class="novel-cover">


<img

    src="images/<?php echo htmlspecialchars(

        $novel['image']

    ); ?>"

    alt="<?php echo htmlspecialchars(

        $novel['title']

    ); ?>"

>


</div>


<div class="novel-info">


<h2>

<?php echo htmlspecialchars(

    $novel['title']

); ?>

</h2>


<p>


<strong>

Author:

</strong>


<?php echo htmlspecialchars(

    $novel['author']

); ?>


</p>


<p>


<strong>

Genre:

</strong>


<?php echo htmlspecialchars(

    $novel['genre']

); ?>


</p>


<p>


<strong>

Access:

</strong>


<?php echo htmlspecialchars(

    $novel['access_type']

); ?>


</p>


<p>


<?php echo nl2br(

    htmlspecialchars(

        $novel['description']

    )

); ?>


</p>


<br>


<h3>

⭐ Rating

</h3>


<p>


<?php


if (

    $totalReviews > 0

) {


    echo $averageRating .

        " / 5 (" .

        $totalReviews .

        " Reviews)";


} else {


    echo "No reviews yet.";


}


?>


</p>


<br>


<div class="card-buttons">


<a

    href="add_favorite.php?id=<?php echo (int)$novel['id']; ?>"

>


<button>


❤️ Add to Favorites


</button>


</a>


<?php if ($continueChapter) { ?>


<a

    href="read.php?chapter=<?php echo (int)$continueChapter['chapter_id']; ?>"

>


<button class="hero-btn">


Continue Reading


</button>


</a>


<?php } ?>


</div>


</div>


</div>


<hr>


<br>


<h2 class="section-title">


📖 Chapters


</h2>


<br>


<div class="chapter-list">


<?php


if (

    mysqli_num_rows($chapters) > 0

) {


    while (

        $chapter = mysqli_fetch_assoc($chapters)

    ) {


?>


<div class="chapter-card">


<h3>


<?php echo htmlspecialchars(

    $chapter['chapter_title']

); ?>


</h3>


<p>


Access:


<strong>


<?php echo htmlspecialchars(

    $chapter['access_type']

); ?>


</strong>


</p>


<?php


if (

    $chapter['access_type'] === "Premium"

) {


    if (

        $user['subscription'] === "Premium"

        ||

        $user['subscription'] === "VIP"

    ) {


?>


<a

    href="read.php?chapter=<?php echo (int)$chapter['id']; ?>"

>


<button class="hero-btn">


📖 Read Chapter


</button>


</a>


<?php


    } else {


?>


<a href="subscribe.php">


<button class="premium-btn">


🔒 Premium Chapter


</button>


</a>


<?php


    }


} else {


?>


<a

    href="read.php?chapter=<?php echo (int)$chapter['id']; ?>"

>


<button>


📖 Read Chapter


</button>


</a>


<?php


}


?>


</div>


<?php


    }


} else {


?>


<div class="empty-state">


<h2>


No Chapters Yet


</h2>


<p>


The author hasn't uploaded any chapters yet.


</p>


</div>


<?php


}


?>


</div>


<hr>


<br>


<h2 style="text-align:center;">


⭐ Reviews


</h2>


<br>


<div class="review-container">


<?php


if (

    mysqli_num_rows($reviews) > 0

) {


    while (

        $review = mysqli_fetch_assoc($reviews)

    ) {


?>


<div class="review-card">


<h4>


<?php echo htmlspecialchars(

    $review['user_email']

); ?>


</h4>


<p>


Rating:


<strong>


<?php echo (int)$review['rating']; ?>/5


</strong>


</p>


<p>


<?php echo nl2br(

    htmlspecialchars(

        $review['comment']

    )

); ?>


</p>


<small>


<?php echo htmlspecialchars(

    $review['created_at']

); ?>


</small>


</div>


<?php


    }


} else {


?>


<div class="empty-state">


<p>


No reviews have been posted yet.


</p>


</div>


<?php


}


?>


</div>


</section>


<!-- ==========================
     FOOTER
========================== -->


<footer class="footer">


<p>


&copy;

<?php echo date("Y"); ?>


NovaReads. All Rights Reserved.


</p>


</footer>


</body>


</html>