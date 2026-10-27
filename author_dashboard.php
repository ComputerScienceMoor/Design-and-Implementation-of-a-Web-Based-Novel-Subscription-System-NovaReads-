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
   GET USER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

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


if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit();

}


/* =====================================
   CHECK AUTHOR ROLE
===================================== */

if (

    $user['role'] !== "author" &&

    $user['role'] !== "admin"

) {

    header("Location: dashboard.php");

    exit();

}


/* =====================================
   ADD NOVEL
===================================== */

if (isset($_POST['add_novel'])) {


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


    $access_type =

        $_POST['access_type'] ?? "Free";


    /* VALIDATE ACCESS TYPE */

    if (

        $access_type !== "Free" &&

        $access_type !== "Premium"

    ) {

        $access_type = "Free";

    }


    /* VALIDATE FIELDS */

    if (

        empty($title) ||

        empty($author) ||

        empty($genre) ||

        empty($description)

    ) {


        $_SESSION['message'] =

            "Please fill in all fields.";


        $_SESSION['message_type'] =

            "error";


        header(

            "Location: author_dashboard.php"

        );


        exit();

    }


    /* =====================================
       IMAGE UPLOAD
    ===================================== */

    $imageName = "";


    if (

        isset($_FILES['image']) &&

        $_FILES['image']['error']

        === UPLOAD_ERR_OK

    ) {


        $extension = strtolower(

            pathinfo(

                $_FILES['image']['name'],

                PATHINFO_EXTENSION

            )

        );


        $allowedExtensions = [

            "jpg",

            "jpeg",

            "png",

            "webp"

        ];


        if (

            in_array(

                $extension,

                $allowedExtensions

            )

        ) {


            $imageName =

                time()

                . "_"

                . uniqid()

                . "."

                . $extension;


            $uploadPath =

                "images/" . $imageName;


            move_uploaded_file(

                $_FILES['image']['tmp_name'],

                $uploadPath

            );

        }

    }


    /* =====================================
       INSERT NOVEL
    ===================================== */

    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO novels

        (

            title,

            author,

            genre,

            description,

            image,

            access_type,

            author_id

        )

        VALUES (?, ?, ?, ?, ?, ?, ?)"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "ssssssi",

        $title,

        $author,

        $genre,

        $description,

        $imageName,

        $access_type,

        $user_id

    );


    if (

        mysqli_stmt_execute($stmt)

    ) {


        $_SESSION['message'] =

            "Novel added successfully.";


        $_SESSION['message_type'] =

            "success";


    } else {


        $_SESSION['message'] =

            "Failed to add novel: "

            . mysqli_error($conn);


        $_SESSION['message_type'] =

            "error";

    }


    mysqli_stmt_close($stmt);


    header(

        "Location: author_dashboard.php"

    );


    exit();

}


/* =====================================
   GET AUTHOR'S NOVELS
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT *

     FROM novels

     WHERE author_id = ?

     ORDER BY id DESC"

);


mysqli_stmt_bind_param(

    $stmt,

    "i",

    $user_id

);


mysqli_stmt_execute($stmt);


$novels = mysqli_stmt_get_result($stmt);


/* =====================================
   GET NOVELS FOR CHAPTER FORM
===================================== */

$chapter_novels = mysqli_query(

    $conn,

    "SELECT id, title

     FROM novels

     WHERE author_id = $user_id

     ORDER BY title ASC"

);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta

```
name="viewport"

content="width=device-width, initial-scale=1.0"
```

>

<title>

Author Dashboard | NovaReads

</title>

<link

```
rel="stylesheet"

href="novel.css"
```

>

</head>

<body>

<?php include("toast.php"); ?>

<header>

<nav>

<h1 class="logo">

NovaReads

</h1>

<div class="nav-links">

<a href="dashboard.php">

Home

</a>

<a href="author_dashboard.php">

My Novels

</a>

<a href="profile.php">

Profile

</a>

<a href="logout.php">

Logout

</a>

</div>

</nav>

</header>

<main class="dashboard">

<h2 class="section-title">

Author Dashboard

<p>

Welcome,

<strong>

<?php echo htmlspecialchars(

    $user['fullname']

); ?>

</strong>

</p>

</h2>

<!-- =====================================
     ADD NOVEL FORM
===================================== -->

<center>
<div class="form-container">

<h2>
Add New Novel
</h2>

<form

    method="POST"

    enctype="multipart/form-data"

>


<!-- ================================
     NOVEL INFORMATION
================================= -->


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

    value="<?php echo htmlspecialchars(

        $user['fullname']

    ); ?>"

    readonly

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

    placeholder="Write a description of your novel..."

    required

></textarea>


<label>

Access Type

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


<br>


<hr>


<!-- ================================
     CHAPTER INFORMATION
================================= -->



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


<br>


<button

    type="submit"

    name="add_novel"

>

Add Novel and Chapter

</button>


</form>

</div>

</center>

<!-- =====================================
     AUTHOR NOVELS
===================================== -->

<h2 class="section-title">

My Novels

</h2>

<div class="novel-container">

<?php if (

    $novels &&

    mysqli_num_rows($novels) > 0

) { ?>

<?php while (

    $novel =

    mysqli_fetch_assoc($novels)

) { ?>

<div class="novel-card">

<?php


$imageName =

    !empty($novel['image']) &&

    file_exists(

        "images/"

        . $novel['image']

    )

    ? $novel['image']

    : "placeholder.svg";


?>

<img

```
src="images/<?php echo htmlspecialchars(

    $imageName

); ?>"

alt="<?php echo htmlspecialchars(

    $novel['title']

); ?>"
```

>

<h3>

<?php echo htmlspecialchars(

    $novel['title']

); ?>

</h3>

<p>

<strong>

Genre:

</strong>

<?php echo htmlspecialchars(

    $novel['genre']

); ?>

</p>

<p>

<strong>

Author:

</strong>

<?php echo htmlspecialchars(

    $novel['author']

); ?>

</p>

<p>

<strong>

Access:

</strong>

<?php echo htmlspecialchars(

    $novel['access_type']

); ?>

</p>

<div class="card-buttons">

<a

```
href="manage_chapters.php?id=<?php echo

    (int)$novel['id'];

?>"

class="hero-btn"
```

>

Manage Chapters

</a>

<a

```
href="novel.php?id=<?php echo

    (int)$novel['id'];

?>"

class="favorite-btn"
```

>

View Novel

</a>

<a

```
href="edit_novel.php?id=<?php echo

    (int)$novel['id'];

?>"

class="edit-btn"
```

>

Edit Novel

</a>

<a

```
href="delete_novel.php?id=<?php echo

    (int)$novel['id'];

?>"

class="delete-btn"

onclick="return confirm(

    'Are you sure you want to delete this novel?'

);"
```

>

Delete Novel

</a>

</div>

</div>

<?php } ?>

<?php } else { ?>

<div class="empty-state">

<h2>

You have not published any novels yet.

</h2>

<p>

Add your first novel using the form above.

</p>

</div>

<?php } ?>

</div>

</main>

</body>

</html>
