<?php

session_start();

include("connect.php");
include("subscription_check.php");


/* =====================================
   LOGIN CHECK
===================================== */

if (!isset($_SESSION['email'])) {

    header("Location: login.php");

    exit();

}


$email = $_SESSION['email'];


/* =====================================
   GET CURRENT USER
===================================== */

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


/* =====================================
   VALIDATE CHAPTER ID
===================================== */

if (

    !isset($_GET['chapter']) ||

    (int) $_GET['chapter'] <= 0

) {


    $_SESSION['message'] =

        "Invalid chapter.";


    $_SESSION['message_type'] =

        "error";


    header("Location: dashboard.php");

    exit();

}


$chapter_id = (int) $_GET['chapter'];


/* =====================================
   GET CHAPTER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT

        chapters.*,

        novels.title,

        novels.id AS novel_id,

        novels.access_type AS novel_access

     FROM chapters

     INNER JOIN novels

     ON chapters.novel_id = novels.id

     WHERE chapters.id = ?"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $chapter_id

);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$chapter = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/* =====================================
   CHECK CHAPTER EXISTS
===================================== */

if (!$chapter) {


    $_SESSION['message'] =

        "Chapter not found.";


    $_SESSION['message_type'] =

        "error";


    header("Location: dashboard.php");

    exit();

}


$novel_id = (int) $chapter['novel_id'];


/* =====================================
   PREMIUM CHECK
===================================== */

if (

    $chapter['access_type'] === "Premium" &&

    $user['subscription'] !== "Premium" &&

    $user['subscription'] !== "VIP"

) {


    $_SESSION['message'] =

        "This chapter requires a Premium or VIP subscription.";


    $_SESSION['message_type'] =

        "warning";


    header("Location: subscribe.php");

    exit();

}


/* =====================================
   SAVE READING PROGRESS
===================================== */

$email_safe = mysqli_real_escape_string(

    $conn,

    $email

);


$check = mysqli_query(

    $conn,

    "SELECT *

     FROM reading_progress

     WHERE user_email = '$email_safe'

     AND novel_id = '$novel_id'"

);


if (

    mysqli_num_rows($check) > 0

) {


    mysqli_query(

        $conn,

        "UPDATE reading_progress

         SET chapter_id = '$chapter_id'

         WHERE user_email = '$email_safe'

         AND novel_id = '$novel_id'"

    );


} else {


    mysqli_query(

        $conn,

        "INSERT INTO reading_progress

        (

            user_email,

            novel_id,

            chapter_id

        )

        VALUES

        (

            '$email_safe',

            '$novel_id',

            '$chapter_id'

        )"

    );

}


/* =====================================
   GET NEXT CHAPTER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM chapters

     WHERE novel_id = ?

     AND id > ?

     ORDER BY id ASC

     LIMIT 1"

);


mysqli_stmt_bind_param(

    $stmt,

    "ii",

    $novel_id,

    $chapter_id

);


mysqli_stmt_execute($stmt);


$nextResult = mysqli_stmt_get_result($stmt);


$nextChapter = mysqli_fetch_assoc($nextResult);


mysqli_stmt_close($stmt);


/* =====================================
   GET PREVIOUS CHAPTER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM chapters

     WHERE novel_id = ?

     AND id < ?

     ORDER BY id DESC

     LIMIT 1"

);


mysqli_stmt_bind_param(

    $stmt,

    "ii",

    $novel_id,

    $chapter_id

);


mysqli_stmt_execute($stmt);


$previousResult = mysqli_stmt_get_result($stmt);


$previousChapter = mysqli_fetch_assoc($previousResult);


mysqli_stmt_close($stmt);

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

    $chapter['chapter_title']

); ?>

|

NovaReads

</title>


<link

    rel="stylesheet"

    href="novel.css"

>


</head>


<body>


<?php include("toast.php"); ?>


<!-- =====================================
     NAVIGATION
===================================== -->


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


<!-- =====================================
     HERO
===================================== -->


<div class="hero-dashboard">


<div class="hero-overlay">


<h1>

<?php echo htmlspecialchars(

    $chapter['title']

); ?>

</h1>


<p>

<?php echo htmlspecialchars(

    $chapter['chapter_title']

); ?>

</p>


</div>


</div>


<!-- =====================================
     READING SECTION
===================================== -->


<section class="reading-section">


<div class="reader-card">


<div class="reader-header">


<h2>

<?php echo htmlspecialchars(

    $chapter['chapter_title']

); ?>

</h2>


<p>


Novel:


<strong>

<?php echo htmlspecialchars(

    $chapter['title']

); ?>

</strong>


</p>


</div>


<div class="reader-actions">


<a href="novel.php?id=<?php echo $novel_id; ?>">


<button>

← Back To Novel

</button>


</a>


</div>


</div>


<!-- =====================================
     CHAPTER CONTENT
===================================== -->


<div class="chapter-content">


<?php


echo nl2br(

    htmlspecialchars(

        $chapter['chapter_content']

    )

);


?>


</div>


<!-- =====================================
     CHAPTER NAVIGATION
===================================== -->


<div class="chapter-navigation">


<?php if ($previousChapter) { ?>


<a href="read.php?chapter=<?php echo (int) $previousChapter['id']; ?>">


<button>


⬅ Previous Chapter


</button>


</a>


<?php } ?>


<?php if ($nextChapter) { ?>


<a href="read.php?chapter=<?php echo (int) $nextChapter['id']; ?>">


<button class="hero-btn">


Next Chapter ➡


</button>


</a>


<?php } ?>


</div>


<!-- =====================================
     END OF BOOK
===================================== -->


<?php if (!$nextChapter) { ?>


<div class="end-of-book">


<h2>


🎉 Congratulations!


</h2>


<p>


You've reached the latest chapter available for this novel.


</p>


<br>


<a href="novel.php?id=<?php echo $novel_id; ?>">


<button>


Back To Novel


</button>


</a>


</div>


<?php } ?>


</section>


<!-- =====================================
     FOOTER
===================================== -->


<footer class="footer">


<div class="footer-content">


<p>


&copy;


<?php echo date("Y"); ?>


NovaReads. All Rights Reserved.


</p>


<p>


Happy Reading 📚


</p>


</div>


</footer>


</body>


</html>