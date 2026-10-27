<?php

session_start();

include("connect.php");


/* =====================================
   CHECK LOGIN
===================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit();

}


$user_id = $_SESSION['user_id'];


/* =====================================
   GET CURRENT USER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT role
     FROM users
     WHERE id = ?"

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $user_id

);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =====================================
   CHECK USER ROLE
===================================== */

if (

    !$user ||

    (

        $user['role'] !== "admin" &&

        $user['role'] !== "author"

    )

) {

    $_SESSION['message'] = "Access denied.";

    $_SESSION['message_type'] = "error";

    header("Location: dashboard.php");

    exit();

}


/* =====================================
   GET NOVEL ID
===================================== */

$novel_id = isset($_GET['id'])

    ? (int) $_GET['id']

    : 0;


/* =====================================
   CHECK VALID NOVEL
===================================== */

if ($novel_id <= 0) {

    $_SESSION['message'] = "Invalid novel.";

    $_SESSION['message_type'] = "error";

    header("Location: dashboard.php");

    exit();

}


/* =====================================
   AUTHOR OWNERSHIP CHECK
===================================== */

if ($user['role'] === "author") {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT id
         FROM novels
         WHERE id = ?
         AND author_id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "ii",

        $novel_id,

        $user_id

    );


    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) === 0) {

        mysqli_stmt_close($stmt);

        $_SESSION['message'] =

            "You can only manage your own novels.";

        $_SESSION['message_type'] = "error";

        header("Location: author_dashboard.php");

        exit();

    }


    mysqli_stmt_close($stmt);

}


/* =====================================
   GET NOVEL DETAILS
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT title
     FROM novels
     WHERE id = ?"

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$novel = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$novel) {

    $_SESSION['message'] = "Novel not found.";

    $_SESSION['message_type'] = "error";

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

     WHERE novel_id = ?

     ORDER BY id ASC"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $novel_id

);


mysqli_stmt_execute($stmt);


$query = mysqli_stmt_get_result($stmt);

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

Manage Chapters | NovaReads

</title>

<link

    rel="stylesheet"

    href="novel.css"

>

</head>

<body>


<?php include("toast.php"); ?>


<nav>

<h1 class="logo">

NovaReads

</h1>


<div class="nav-links">


<?php if ($user['role'] === "admin") { ?>

<a href="admin.php">

Admin Panel

</a>

<?php } else { ?>

<a href="author_dashboard.php">

Author Dashboard

</a>

<?php } ?>


<a href="dashboard.php">

Dashboard

</a>


<a href="logout.php">

Logout

</a>


</div>

</nav>


<div class="admin-container">


<div class="admin-header">


<div>


<h1>

Manage Chapters

</h1>


<h3>

<?php echo htmlspecialchars($novel['title']); ?>

</h3>

</div>


<a

    href="add_chapter.php?id=<?php echo $novel_id; ?>"

>


<button class="add-btn">

+ Add Chapter

</button>


</a>


</div>


<div class="table-container">


<table>


<thead>


<tr>


<th>

ID

</th>


<th>

Chapter

</th>


<th>

Access

</th>


<th>

Created

</th>


<th>

Actions

</th>


</tr>


</thead>


<tbody>


<?php if (

    $query &&

    mysqli_num_rows($query) > 0

) { ?>


<?php while (

    $row = mysqli_fetch_assoc($query)

) { ?>


<tr>


<td>

<?php echo (int) $row['id']; ?>

</td>


<td>

<?php echo htmlspecialchars(

    $row['chapter_title']

); ?>

</td>


<td>

<?php echo htmlspecialchars(

    $row['access_type']

); ?>

</td>


<td>

<?php echo htmlspecialchars(

    $row['created_at']

); ?>

</td>


<td>


<a

    href="edit_chapter.php?id=<?php echo (int) $row['id']; ?>"

>


<button class="edit-btn">

Edit

</button>


</a>


<a

    href="delete_chapter.php?id=<?php echo (int) $row['id']; ?>"

    onclick="return confirm('Delete this chapter?');"

>


<button class="delete-btn">

Delete

</button>


</a>


</td>


</tr>


<?php } ?>


<?php } else { ?>


<tr>


<td

    colspan="5"

    class="empty-table"

>


No chapters found.


</td>


</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>


</body>

</html>