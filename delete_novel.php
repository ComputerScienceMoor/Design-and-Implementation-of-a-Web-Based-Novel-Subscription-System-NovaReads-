<?php

session_start();
include("connect.php");

/* =====================================
   ADMIN CHECK
===================================== */

if (!isset($_SESSION['email'])) {

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT role FROM users WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if (!$user || $user['role'] != "admin") {

    $_SESSION['message'] = "Access denied.";
    $_SESSION['message_type'] = "error";

    header("Location: dashboard.php");
    exit();
}

/* =====================================
   VALIDATE NOVEL ID
===================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    $_SESSION['message'] = "Invalid novel.";
    $_SESSION['message_type'] = "error";

    header("Location: admin.php");
    exit();
}

$novel_id = (int) $_GET['id'];

/* =====================================
   CHECK NOVEL EXISTS
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM novels WHERE id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$novel = mysqli_fetch_assoc($result);

if (!$novel) {

    $_SESSION['message'] = "Novel not found.";
    $_SESSION['message_type'] = "error";

    header("Location: admin.php");
    exit();
}

/* =====================================
   DELETE RELATED CHAPTERS
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM chapters WHERE novel_id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

mysqli_stmt_execute($stmt);

/* =====================================
   DELETE FAVORITES
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM favorites WHERE novel_id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

mysqli_stmt_execute($stmt);

/* =====================================
   DELETE NOVEL
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM novels WHERE id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $novel_id
);

if (mysqli_stmt_execute($stmt)) {

    /* Delete Cover Image */

    if (
        !empty($novel['image']) &&
        file_exists("images/" . $novel['image'])
    ) {

        unlink("images/" . $novel['image']);
    }

    $_SESSION['message'] = "Novel deleted successfully.";
    $_SESSION['message_type'] = "success";

} else {

    $_SESSION['message'] = "Unable to delete novel.";
    $_SESSION['message_type'] = "error";
}

header("Location: admin.php");
exit();

?>