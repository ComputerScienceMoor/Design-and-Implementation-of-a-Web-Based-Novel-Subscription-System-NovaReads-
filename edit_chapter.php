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
   GET CHAPTER
===================================== */

if ($user['role'] === "author") {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT chapters.*,
                novels.title AS novel_title

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

        "SELECT chapters.*,
                novels.title AS novel_title

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

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$chapter = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


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
   UPDATE CHAPTER
===================================== */

if (isset($_POST['update_chapter'])) {


    $title = trim(

        $_POST['chapter_title'] ?? ""

    );


    $content = trim(

        $_POST['chapter_content'] ?? ""

    );


    $access = $_POST['access_type'] ?? "Free";


    /* VALIDATE ACCESS TYPE */

    if (

        $access !== "Free" &&

        $access !== "Premium"

    ) {

        $access = "Free";

    }


    /* VALIDATE FIELDS */

    if (

        empty($title) ||

        empty($content)

    ) {


        $_SESSION['message'] =

            "All fields are required.";

        $_SESSION['message_type'] =

            "warning";


    } else {


        $stmt = mysqli_prepare(

            $conn,

            "UPDATE chapters

             SET

             chapter_title = ?,

             chapter_content = ?,

             access_type = ?

             WHERE id = ?"

        );


        mysqli_stmt_bind_param(

            $stmt,

            "sssi",

            $title,

            $content,

            $access,

            $chapter_id

        );


        if (mysqli_stmt_execute($stmt)) {


            mysqli_stmt_close($stmt);


            $_SESSION['message'] =

                "Chapter updated successfully.";

            $_SESSION['message_type'] =

                "success";


            header(

                "Location: manage_chapters.php?id="

                . $chapter['novel_id']

            );

            exit();


        } else {


            $_SESSION['message'] =

                "Failed to update chapter: "

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

Edit Chapter | NovaReads

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


<a

    href="manage_chapters.php?id=<?php echo (int) $chapter['novel_id']; ?>"

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

Edit Chapter

</h2>


<p>


<strong>

Novel:

</strong>


<?php echo htmlspecialchars(

    $chapter['novel_title']

); ?>


</p>


<form

    method="POST"

>


<label>

Chapter Title

</label>


<input

    type="text"

    name="chapter_title"

    value="<?php echo htmlspecialchars(

        $chapter['chapter_title']

    ); ?>"

    required

>


<label>

Chapter Content

</label>


<textarea

    name="chapter_content"

    rows="15"

    required

><?php echo htmlspecialchars(

    $chapter['chapter_content']

); ?></textarea>


<label>

Access Type

</label>


<select

    name="access_type"

>


<option

    value="Free"

    <?php

    if (

        $chapter['access_type'] === "Free"

    ) {

        echo "selected";

    }

    ?>

>

Free

</option>


<option

    value="Premium"

    <?php

    if (

        $chapter['access_type'] === "Premium"

    ) {

        echo "selected";

    }

    ?>

>

Premium

</option>


</select>


<button

    type="submit"

    name="update_chapter"

>


Update Chapter


</button>


</form>


</div>


</div>


</body>

</html>