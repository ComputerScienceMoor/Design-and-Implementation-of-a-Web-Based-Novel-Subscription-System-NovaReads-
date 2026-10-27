<?php

session_start();
include("connect.php");

if (!isset($_GET['novel_id']) || !is_numeric($_GET['novel_id'])) {

    header("Location: dashboard.php");
    exit();

}

$novel_id = (int)$_GET['novel_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT title FROM novels WHERE id=?"
);

mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$novel = mysqli_fetch_assoc($result);

if (!$novel) {

    header("Location: dashboard.php");
    exit();

}

$stmt = mysqli_prepare(

    $conn,

    "SELECT *
     FROM reviews
     WHERE novel_id=?
     ORDER BY created_at DESC"

);

mysqli_stmt_bind_param($stmt, "i", $novel_id);

mysqli_stmt_execute($stmt);

$reviews = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>

Reviews

</title>

<link rel="stylesheet" href="novel.css">

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

<a href="logout.php">

Logout

</a>

</div>

</nav>

<div class="dashboard">

<h2>

Reviews for

<?php echo htmlspecialchars($novel['title']); ?>

</h2>

<hr><br>

<?php

if(isset($_SESSION['email'])){

?>

<form action="submit_review.php" method="POST">

<input
type="hidden"
name="novel_id"
value="<?php echo $novel_id; ?>">

<label>Rating</label>

<select name="rating">

<option value="5">⭐⭐⭐⭐⭐</option>
<option value="4">⭐⭐⭐⭐</option>
<option value="3">⭐⭐⭐</option>
<option value="2">⭐⭐</option>
<option value="1">⭐</option>

</select>

<br><br>

<textarea

name="review"

rows="6"

placeholder="Write your review..."

required>

</textarea>

<br><br>

<button
type="submit">

Submit Review

</button>

</form>

<hr><br>

<?php

}

if(mysqli_num_rows($reviews)>0){

while($row=mysqli_fetch_assoc($reviews)){

?>

<div class="review-card">

<h3>

<?php

echo str_repeat("⭐",$row['rating']);

?>

</h3>

<p>

<?php

echo nl2br(htmlspecialchars($row['review']));

?>

</p>

<br>

<small>

By

<?php

echo htmlspecialchars($row['user_email']);

?>

|

<?php

echo $row['created_at'];

?>

</small>

<?php

if(

isset($_SESSION['email']) &&

$_SESSION['email']==$row['user_email']

){

?>

<br><br>

<a

href="delete_review.php?id=<?php echo $row['id']; ?>"

onclick="return confirm('Delete review?')">

<button class="delete-btn">

Delete

</button>

</a>

<?php

}

?>

</div>

<br>

<?php

}

}else{

?>

<p>

No reviews yet.

Be the first!

</p>

<?php

}

?>

</div>

</body>

</html>