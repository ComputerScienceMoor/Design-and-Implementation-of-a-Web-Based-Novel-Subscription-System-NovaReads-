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
   VALIDATE NOVEL ID
===================================== */

if (

    !isset($_GET['id']) ||

    !is_numeric($_GET['id'])

) {

    $_SESSION['message'] = "Invalid novel.";

    $_SESSION['message_type'] = "error";


    if ($user['role'] === "author") {

        header("Location: author_dashboard.php");

    } else {

        header("Location: admin.php");

    }

    exit();

}


$novel_id = (int) $_GET['id'];


/* =====================================
   GET NOVEL
===================================== */

if ($user['role'] === "author") {


    $stmt = mysqli_prepare(

        $conn,

        "SELECT *

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

        "SELECT *

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
   CHECK NOVEL
===================================== */

if (!$novel) {

    $_SESSION['message'] =

        "Novel not found or access denied.";

    $_SESSION['message_type'] = "error";


    if ($user['role'] === "author") {

        header("Location: author_dashboard.php");

    } else {

        header("Location: admin.php");

    }

    exit();

}


/* =====================================
   UPDATE NOVEL
===================================== */

if (isset($_POST['update_novel'])) {


    $title = trim(

        $_POST['title'] ?? ""

    );


    $author = trim(

        $_POST['author'] ?? ""

    );


    $genre = trim(

        $_POST['genre'] ?? ""

    );


    $description = trim(

        $_POST['description'] ?? ""

    );


    $access_type = $_POST['access_type'] ?? "Free";


    /* VALIDATE ACCESS TYPE */

    if (

        $access_type !== "Free" &&

        $access_type !== "Premium"

    ) {

        $access_type = "Free";

    }


    /* VALIDATE TEXT FIELDS */

    if (

        empty($title) ||

        empty($author) ||

        empty($genre) ||

        empty($description)

    ) {

        $_SESSION['message'] =

            "Please fill in all required fields.";

        $_SESSION['message_type'] =

            "warning";

    }


    else {


        $image = $novel['image'];


        /* =====================================
           HANDLE NEW IMAGE
        ===================================== */


        if (

            isset($_FILES['image']) &&

            $_FILES['image']['error'] === UPLOAD_ERR_OK

        ) {


            $allowed_types = [

                "image/jpeg",

                "image/png",

                "image/gif",

                "image/webp"

            ];


            $file_type =

                $_FILES['image']['type'];


            if (

                !in_array(

                    $file_type,

                    $allowed_types

                )

            ) {


                $_SESSION['message'] =

                    "Invalid image type.";

                $_SESSION['message_type'] =

                    "error";


            } else {


                $extension =

                    pathinfo(

                        $_FILES['image']['name'],

                        PATHINFO_EXTENSION

                    );


                $new_image =

                    time()

                    . "_"

                    . uniqid()

                    . "."

                    . $extension;


                $target =

                    "images/"

                    . $new_image;


                if (

                    move_uploaded_file(

                        $_FILES['image']['tmp_name'],

                        $target

                    )

                ) {


                    /* DELETE OLD IMAGE */

                    if (

                        !empty($novel['image']) &&

                        file_exists(

                            "images/"

                            . $novel['image']

                        )

                    ) {


                        unlink(

                            "images/"

                            . $novel['image']

                        );

                    }


                    $image = $new_image;

                }

            }

        }


        /* =====================================
           UPDATE DATABASE
        ===================================== */


        if (

            !isset($_SESSION['message']) ||

            $_SESSION['message_type'] !== "error"

        ) {


            $stmt = mysqli_prepare(

                $conn,

                "UPDATE novels

                 SET

                 title = ?,

                 author = ?,

                 genre = ?,

                 description = ?,

                 image = ?,

                 access_type = ?

                 WHERE id = ?"

            );


            mysqli_stmt_bind_param(

                $stmt,

                "ssssssi",

                $title,

                $author,

                $genre,

                $description,

                $image,

                $access_type,

                $novel_id

            );


            if (

                mysqli_stmt_execute($stmt)

            ) {


                mysqli_stmt_close($stmt);


                $_SESSION['message'] =

                    "Novel updated successfully.";

                $_SESSION['message_type'] =

                    "success";


                if (

                    $user['role'] === "author"

                ) {

                    header(

                        "Location: author_dashboard.php"

                    );

                } else {

                    header(

                        "Location: admin.php"

                    );

                }


                exit();


            } else {


                $_SESSION['message'] =

                    "Failed to update novel.";

                $_SESSION['message_type'] =

                    "error";


                mysqli_stmt_close($stmt);

            }

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

Edit Novel | NovaReads

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


<div class="auth-page">


<div class="form-container">


<h2>

Edit Novel

</h2>


<form

    method="POST"

    enctype="multipart/form-data"

>


<label>

Title

</label>


<input

    type="text"

    name="title"

    value="<?php echo htmlspecialchars(

        $novel['title']

    ); ?>"

    required

>


<label>

Author Name

</label>


<input

    type="text"

    name="author"

    value="<?php echo htmlspecialchars(

        $novel['author']

    ); ?>"

    required

>


<label>

Genre

</label>


<input

    type="text"

    name="genre"

    value="<?php echo htmlspecialchars(

        $novel['genre']

    ); ?>"

    required

>


<label>

Description

</label>


<textarea

    name="description"

    rows="8"

    required

><?php echo htmlspecialchars(

    $novel['description']

); ?></textarea>


<label>

Current Cover

</label>


<?php if (

    !empty($novel['image']) &&

    file_exists(

        "images/" . $novel['image']

    )

) { ?>


<img

    src="images/<?php echo htmlspecialchars(

        $novel['image']

    ); ?>"

    alt="Current Novel Cover"

    style="

        width:150px;

        margin-bottom:20px;

        border-radius:10px;

    "

>


<?php } else { ?>


<p>

No cover image uploaded.

</p>


<?php } ?>


<label>

New Cover

<span>

(Optional)

</span>

</label>


<input

    type="file"

    name="image"

    accept="image/jpeg,image/png,image/gif,image/webp"

>


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

        $novel['access_type']

        ===

        "Free"

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

        $novel['access_type']

        ===

        "Premium"

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

    name="update_novel"

>


Update Novel


</button>


</form>


</div>


</div>


</body>

</html>