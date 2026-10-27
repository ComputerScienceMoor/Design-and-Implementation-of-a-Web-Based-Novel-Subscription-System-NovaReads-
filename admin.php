<?php

session_start();

include("connect.php");


/* =====================================
   CHECK IF USER IS LOGGED IN
===================================== */

if (!isset($_SESSION['email'])) {

    header("Location: login.php");

    exit();

}


/* =====================================
   GET CURRENT USER
===================================== */

$email = $_SESSION['email'];

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

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


/* =====================================
   CHECK ADMIN ROLE
===================================== */

if (

    !$user ||

    strtolower(trim($user['role'])) !== "admin"

) {

    $_SESSION['message'] =

    "Access denied. Admins only.";

    $_SESSION['message_type'] =

    "error";


    header("Location: dashboard.php");

    exit();

}

/* =====================================
   DASHBOARD STATISTICS
===================================== */


/* TOTAL USERS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$totalUsers = mysqli_fetch_assoc($result)['total'];


/* TOTAL NOVELS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM novels"
);

$totalNovels = mysqli_fetch_assoc($result)['total'];


/* TOTAL CHAPTERS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM chapters"
);

$totalChapters = mysqli_fetch_assoc($result)['total'];


/* TOTAL FAVORITES */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM favorites"
);

$totalFavorites = mysqli_fetch_assoc($result)['total'];


/* TOTAL PREMIUM USERS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE subscription = 'Premium'"
);

$totalPremium = mysqli_fetch_assoc($result)['total'];


/* TOTAL VIP USERS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE subscription = 'VIP'"
);

$totalVIP = mysqli_fetch_assoc($result)['total'];


/* =====================================
   GET RECENT NOVELS
===================================== */

$novels = mysqli_query(
    $conn,
    "SELECT *
     FROM novels
     ORDER BY id DESC
     LIMIT 10"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>NovaReads Admin Panel</title>

<link rel="stylesheet"
href="novel.css">

</head>

<body>

<?php include("toast.php"); ?>


<!-- ================================
     NAVIGATION
================================= -->

<nav>

<h1 class="logo">
NovaReads Admin
</h1>

<div class="nav-links">

<a href="dashboard.php">
Dashboard
</a>

<a href="profile.php">
Profile
</a>

<a href="logout.php">
Logout
</a>
<a href="admin_applications.php">
    Author Applications
</a>
</div>

</nav>


<!-- ================================
     ADMIN CONTAINER
================================= -->

<div class="admin-container">


<div class="admin-header">

<h1>
Administrator Dashboard
</h1>

<p>
Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>
</p>

</div>


<!-- ================================
     STATISTICS
================================= -->

<div class="admin-stats">


<div class="admin-stat-card">

<h2>
<?php echo $totalUsers; ?>
</h2>

<p>
Total Users
</p>

</div>


<div class="admin-stat-card">

<h2>
<?php echo $totalNovels; ?>
</h2>

<p>
Total Novels
</p>

</div>


<div class="admin-stat-card">

<h2>
<?php echo $totalChapters; ?>
</h2>

<p>
Total Chapters
</p>

</div>


<div class="admin-stat-card">

<h2>
<?php echo $totalFavorites; ?>
</h2>

<p>
Total Favorites
</p>

</div>


<div class="admin-stat-card">

<h2>
<?php echo $totalPremium; ?>
</h2>

<p>
Premium Users
</p>

</div>


<div class="admin-stat-card">

<h2>
<?php echo $totalVIP; ?>
</h2>

<p>
VIP Users
</p>

</div>


</div>


<!-- ================================
     ADD NOVEL AND CHAPTER
================================= -->

<div class="form-container">

<h2>
Add New Novel
</h2>

<form
    action="add_novel.php"
    method="POST"
    enctype="multipart/form-data"
>

<label>
Novel Title
</label>

<input
    type="text"
    name="title"
    placeholder="Enter novel title"
    required
>


<label>
Author Name
</label>

<input
    type="text"
    name="author"
    placeholder="Enter author name"
    required
>


<label>
Genre
</label>

<input
    type="text"
    name="genre"
    placeholder="Fantasy, Romance, Action..."
    required
>


<label>
Description
</label>

<textarea
    name="description"
    rows="6"
    placeholder="Write a description of the novel..."
    required
></textarea>


<label>
Novel Access Type
</label>

<select
    name="access_type"
    required
>

<option value="Free">
Free
</option>

<option value="Premium">
Premium
</option>

</select>


<label>
Cover Image
</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.webp"
>


<hr>


<h2>
Add First Chapter
</h2>


<label>
Chapter Title
</label>

<input
    type="text"
    name="chapter_title"
    placeholder="Enter chapter title"
    required
>


<label>
Chapter Content
</label>

<input
    type="file"
    name="chapter_file"
    accept=".txt"
    required
>


<small>
Upload your chapter as a .txt file.
</small>


<label>
Chapter Access Type
</label>

<select
    name="chapter_access_type"
    required
>

<option value="Free">
Free
</option>

<option value="Premium">
Premium
</option>

</select>


<button
    type="submit"
    name="add_novel"
>

Add Novel and Chapter

</button>

</form>

</div>

<!-- ================================
     LATEST NOVELS
================================= -->

<h2 class="section-title">
Latest Novels
</h2>


<div class="table-container">

<table>

<thead>

<tr>

<th>ID</th>

<th>Cover</th>

<th>Title</th>

<th>Author</th>

<th>Genre</th>

<th>Access</th>

<th>Actions</th>

</tr>

</thead>


<tbody>


<?php

if (
    $novels &&
    mysqli_num_rows($novels) > 0
) {

    while (
        $row = mysqli_fetch_assoc($novels)
    ) {

?>


<tr>

<td>

<?php echo $row['id']; ?>

</td>


<td>

<?php

if (
    !empty($row['image']) &&
    file_exists("images/" . $row['image'])
) {

?>

<img

src="images/<?php

echo htmlspecialchars(
    $row['image']
);

?>"

class="table-image"

alt="Novel Cover">

<?php

} else {

?>

<span>
No Image
</span>

<?php

}

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['title']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['author']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['genre']
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row['access_type']
);

?>

</td>


<td>


<a

href="edit_novel.php?id=<?php

echo $row['id'];

?>"

class="edit-btn">

Edit

</a>


<a

href="delete_novel.php?id=<?php

echo $row['id'];

?>"

class="delete-btn"

onclick="return confirm(

'Are you sure you want to delete this novel?'

)">

Delete

</a>


</td>

</tr>


<?php

    }

} else {

?>


<tr>

<td

colspan="7"

class="empty-table">

No novels available yet.

</td>

</tr>


<?php

}

?>


</tbody>

</table>

</div>

</div>

</body>

</html>