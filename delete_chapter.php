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
   ALLOW ADMIN OR AUTHOR
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
   VALIDATE CHAPTER ID
===================================== */

if (

    !isset($_GET['id']) ||

    !is_numeric($_GET['id'])

) {

    $_SESSION['message'] = "Invalid chapter.";

    $_SESSION['message_type'] = "error";


    if ($user['role'] === "author") {

        header("Location: author_dashboard.php");

    } else {

        header("Location: admin.php");

    }

    exit();

}


$chapter_id = (int) $_GET['id'];


/* =====================================
   GET CHAPTER AND CHECK OWNERSHIP
===================================== */

if ($user['role'] === "author") {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT chapters.id,
                chapters.novel_id

         FROM chapters

         INNER JOIN novels

         ON chapters.novel_id = novels.id

         WHERE chapters.id = ?

         AND novels.author_id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "ii",

        $chapter_id,

        $user_id

    );


} else {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT id,
                novel_id

         FROM chapters

         WHERE id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "i",

        $chapter_id

    );

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$chapter = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =====================================
   CHECK CHAPTER
===================================== */

if (!$chapter) {


    $_SESSION['message'] =

        "Chapter not found or access denied.";

    $_SESSION['message_type'] = "error";


    if ($user['role'] === "author") {

        header("Location: author_dashboard.php");

    } else {

        header("Location: admin.php");

    }

    exit();

}


/* =====================================
   DELETE CHAPTER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "DELETE FROM chapters
     WHERE id = ?"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $chapter_id

);


if (mysqli_stmt_execute($stmt)) {


    $_SESSION['message'] =

        "Chapter deleted successfully.";

    $_SESSION['message_type'] =

        "success";


} else {


    $_SESSION['message'] =

        "Unable to delete chapter.";

    $_SESSION['message_type'] =

        "error";

}


mysqli_stmt_close($stmt);


/* =====================================
   REDIRECT
===================================== */

if ($user['role'] === "author") {


    header(

        "Location: manage_chapters.php?id="

        . $chapter['novel_id']

    );


} else {


    header(

        "Location: manage_chapters.php?id="

        . $chapter['novel_id']

    );

}


exit();

?>