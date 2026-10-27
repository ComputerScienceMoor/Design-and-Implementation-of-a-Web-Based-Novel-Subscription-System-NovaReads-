<?php

session_start();
include("connect.php");

/* ==========================
   LOGIN CHECK
========================== */

if(!isset($_SESSION['email'])){

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* ==========================
   VALIDATE NOVEL ID
========================== */

if(!isset($_GET['id'])){

    $_SESSION['message'] = "Invalid request.";

    $_SESSION['message_type'] = "error";

    header("Location: favorites.php");
    exit();
}

$novel_id = (int)$_GET['id'];

/* ==========================
   VERIFY FAVORITE EXISTS
========================== */

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

if(mysqli_num_rows($result) == 0){

    $_SESSION['message'] = "Novel not found in favorites.";

    $_SESSION['message_type'] = "warning";

    header("Location: favorites.php");
    exit();
}

/* ==========================
   DELETE FAVORITE
========================== */

$stmt = mysqli_prepare(

    $conn,

    "DELETE FROM favorites
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

/* ==========================
   SUCCESS
========================== */

$_SESSION['message'] = "Novel removed from favorites.";

$_SESSION['message_type'] = "success";

header("Location: favorites.php");

exit();

?>