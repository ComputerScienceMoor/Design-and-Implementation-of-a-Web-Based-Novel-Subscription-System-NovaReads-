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
   GET USER
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname, subscription
     FROM users
     WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$userResult = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($userResult);

if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit();
}

/* =====================================
   SUBSCRIPTION CHECK
===================================== */

if (
    $user['subscription'] != "Premium" &&
    $user['subscription'] != "VIP"
) {

    $_SESSION['message'] =
    "Upgrade to Premium or VIP to access this novel.";

    $_SESSION['message_type'] =
    "warning";

    header("Location: subscribe.php");
    exit();
}

/* =====================================
   VALIDATE NOVEL ID
===================================== */

if (!isset($_GET['id'])) {

    header("Location: dashboard.php");
    exit();
}

$novel_id = (int)$_GET['id'];

/* =====================================
   GET NOVEL
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM novels
     WHERE id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

mysqli_stmt_execute($stmt);

$novelResult = mysqli_stmt_get_result($stmt);

$novel = mysqli_fetch_assoc($novelResult);

if (!$novel) {

    $_SESSION['message'] =
    "Novel not found.";

    $_SESSION['message_type'] =
    "error";

    header("Location: dashboard.php");
    exit();
}

/* =====================================
   GET CHAPTERS
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM chapters
     WHERE novel_id=?
     ORDER BY id ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

mysqli_stmt_execute($stmt);

$chapters = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

<?php echo htmlspecialchars($novel['title']); ?>

</title>

<link rel="stylesheet"
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

<div class="dashboard">

<div class="novel-card"
style="width:80%;margin:auto;">

<img
src="images/<?php echo htmlspecialchars($novel['image']); ?>"
alt="<?php echo htmlspecialchars($novel['title']); ?>">

<h1>

<?php echo htmlspecialchars($novel['title']); ?>

</h1>

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

<p>

<?php echo nl2br(htmlspecialchars($novel['description'])); ?>

</p>

<hr>

<br>

<h2>

📖 Available Chapters

</h2>

<br>

<?php

if(mysqli_num_rows($chapters) > 0){

while($chapter = mysqli_fetch_assoc($chapters)){

?>

<a href="read.php?chapter=<?php echo (int)$chapter['id']; ?>">

<button class="hero-btn">

<?php

echo htmlspecialchars(
$chapter['chapter_title']
);

?>

</button>

</a>

<br><br>

<?php

}

}else{

?>

<p style="text-align:center;">

No chapters available.

</p>

<?php

}

?>

</div>

</div>

</body>

</html>