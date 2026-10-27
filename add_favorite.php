<?php

session_start();

include("connect.php");

/* =====================================
   LOGIN CHECK
===================================== */

if (!isset($_SESSION['email'])) {

    $_SESSION['message'] = "Please login first.";
    $_SESSION['message_type'] = "error";

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* =====================================
   VALIDATE NOVEL ID
===================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    $_SESSION['message'] = "Invalid novel selected.";
    $_SESSION['message_type'] = "error";

    header("Location: dashboard.php");
    exit();
}

$novel_id = (int) $_GET['id'];

/* =====================================
   CHECK IF NOVEL EXISTS
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT id
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

if (mysqli_num_rows($result) == 0) {

    $_SESSION['message'] = "Novel not found.";
    $_SESSION['message_type'] = "error";

    header("Location: dashboard.php");
    exit();
}

/* =====================================
   CHECK DUPLICATE FAVORITE
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT id
     FROM favorites
     WHERE user_email = ?
     AND novel_id = ?"

);

mysqli_stmt_bind_param(

    $stmt,

    "si",

    $email,
    $novel_id

);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    $_SESSION['message'] = "This novel is already in your favorites.";
    $_SESSION['message_type'] = "warning";

    header("Location: dashboard.php");
    exit();
}

/* =====================================
   ADD FAVORITE
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "INSERT INTO favorites
    (
        user_email,
        novel_id
    )
    VALUES
    (
        ?, ?
    )"

);

mysqli_stmt_bind_param(

    $stmt,

    "si",

    $email,
    $novel_id

);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['message'] = "Novel added to favorites successfully!";
    $_SESSION['message_type'] = "success";

} else {

    $_SESSION['message'] = "Unable to add favorite.";
    $_SESSION['message_type'] = "error";
}

header("Location: dashboard.php");
exit();

?>