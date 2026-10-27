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


$user_id = (int) $_SESSION['user_id'];


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
   CHECK ROLE
===================================== */

if (

    !$user ||

    (

        $user['role'] !== "admin" &&

        $user['role'] !== "author"

    )

) {

    $_SESSION['message'] =

        "Access denied.";

    $_SESSION['message_type'] =

        "error";


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
   CHECK NOVEL ID
===================================== */

if ($novel_id <= 0) {

    $_SESSION['message'] =

        "Invalid novel.";

    $_SESSION['message_type'] =

        "error";


    header("Location: dashboard.php");

    exit();

}


/* =====================================
   CHECK NOVEL OWNERSHIP
===================================== */

if ($user['role'] === "author") {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT id, title

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


} else {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT id, title

         FROM novels

         WHERE id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "i",

        $novel_id

    );

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$novel = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =====================================
   CHECK NOVEL EXISTS
===================================== */

if (!$novel) {

    $_SESSION['message'] =

        "Novel not found or access denied.";

    $_SESSION['message_type'] =

        "error";


    if ($user['role'] === "author") {

        header(

            "Location: author_dashboard.php"

        );

    } else {

        header(

            "Location: admin.php"

        );

    }

    exit();

}


/* =====================================
   ADD CHAPTER
===================================== */

if (isset($_POST['add_chapter'])) {


    $chapter_title = trim(

        $_POST['chapter_title'] ?? ""

    );


    $chapter_content = "";


/* =====================================
   READ UPLOADED TXT CHAPTER FILE
===================================== */

    if (

        isset($_FILES['chapter_file']) &&

        $_FILES['chapter_file']['error'] === 0

    ) {


        $fileName = $_FILES['chapter_file']['name'];


        $fileExtension = strtolower(

            pathinfo(

                $fileName,

                PATHINFO_EXTENSION

            )

        );


        if ($fileExtension !== "txt") {


            $_SESSION['message'] =

                "Only TXT chapter files are allowed.";


            $_SESSION['message_type'] =

                "error";


        } else {


            $chapter_content = file_get_contents(

                $_FILES['chapter_file']['tmp_name']

            );

        }

    }


    $access_type = $_POST['access_type'] ?? "Free";


    /* VALIDATE ACCESS TYPE */

    if (

        $access_type !== "Free" &&

        $access_type !== "Premium"

    ) {

        $access_type = "Free";

    }


    /* VALIDATE FIELDS */

    if (

        $chapter_title === "" ||

        $chapter_content === ""

    ) {


        $_SESSION['message'] =

            "Please upload a valid chapter file.";

        $_SESSION['message_type'] =

            "warning";


    } else {


        $stmt = mysqli_prepare(

            $conn,

            "INSERT INTO chapters

            (

                novel_id,

                chapter_title,

                chapter_content,

                access_type

            )

            VALUES (?, ?, ?, ?)"

        );


        mysqli_stmt_bind_param(

            $stmt,

            "isss",

            $novel_id,

            $chapter_title,

            $chapter_content,

            $access_type

        );


        if (

            mysqli_stmt_execute($stmt)

        ) {


            mysqli_stmt_close($stmt);


            $_SESSION['message'] =

                "Chapter added successfully.";

            $_SESSION['message_type'] =

                "success";


            header(

                "Location: manage_chapters.php?id="

                . $novel_id

            );

            exit();


        } else {


            $_SESSION['message'] =

                "Failed to add chapter: "

                . mysqli_error($conn);

            $_SESSION['message_type'] =

                "error";


            mysqli_stmt_close($stmt);

        }

    }

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

Add Chapter | NovaReads

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


<?php if (

    $user['role'] === "admin"

) { ?>


<a href="admin.php">

Admin Panel

</a>


<?php } else { ?>


<a href="author_dashboard.php">

Author Dashboard

</a>


<?php } ?>


<a

    href="manage_chapters.php?id=<?php echo (int)$novel_id; ?>"

>

Manage Chapters

</a>


<a href="dashboard.php">

Dashboard

</a>


<a href="logout.php">

Logout

</a>


</div>


</nav>


<div class="auth-page">


<div class="form-container">


<h2>

Add Chapter

</h2>


<p>


<strong>

Novel:

</strong>


<?php echo htmlspecialchars(

    $novel['title']

); ?>


</p>


<form
    method="POST"
    enctype="multipart/form-data"
>


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


<label>
    Upload Chapter File
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

Access Type

</label>


<select

    name="access_type"

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

    name="add_chapter"

>


Add Chapter


</button>


</form>


</div>


</div>


</body>


</html>