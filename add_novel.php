<?php

session_start();

include("connect.php");


/* =====================================
   CHECK LOGIN
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

mysqli_stmt_close($stmt);


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
   PROCESS FORM
===================================== */

if (isset($_POST['add_novel'])) {


    /* =================================
       GET NOVEL DATA
    ================================= */

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


    /* =================================
       GET CHAPTER DATA
    ================================= */

    $chapter_title = trim(

        $_POST['chapter_title'] ?? ""

    );


    $chapter_access_type =

        $_POST['chapter_access_type'] ?? "Free";


    $chapter_number = 1;


    /* =================================
       VALIDATE NOVEL ACCESS TYPE
    ================================= */

    if (

        $access_type !== "Free" &&

        $access_type !== "Premium"

    ) {

        $access_type = "Free";

    }


    /* =================================
       VALIDATE CHAPTER ACCESS TYPE
    ================================= */

    if (

        $chapter_access_type !== "Free" &&

        $chapter_access_type !== "Premium"

    ) {

        $chapter_access_type = "Free";

    }


    /* =================================
       VALIDATE NOVEL FIELDS
    ================================= */

    if (

        $title === "" ||

        $author === "" ||

        $genre === "" ||

        $description === ""

    ) {

        $_SESSION['message'] =

            "Please fill in all novel fields.";

        $_SESSION['message_type'] =

            "warning";


        header("Location: admin.php");

        exit();

    }


    /* =================================
       VALIDATE CHAPTER TITLE
    ================================= */

    if (

        $chapter_title === ""

    ) {

        $_SESSION['message'] =

            "Please enter a chapter title.";

        $_SESSION['message_type'] =

            "warning";


        header("Location: admin.php");

        exit();

    }


    /* =================================
       VALIDATE CHAPTER FILE
    ================================= */

    if (

        !isset($_FILES['chapter_file']) ||

        $_FILES['chapter_file']['error']

        !== UPLOAD_ERR_OK

    ) {

        $_SESSION['message'] =

            "Please upload a valid TXT chapter file.";

        $_SESSION['message_type'] =

            "warning";


        header("Location: admin.php");

        exit();

    }


    $chapter_original_name =

        $_FILES['chapter_file']['name'];


    $chapter_extension = strtolower(

        pathinfo(

            $chapter_original_name,

            PATHINFO_EXTENSION

        )

    );


    if (

        $chapter_extension !== "txt"

    ) {

        $_SESSION['message'] =

            "Only TXT chapter files are allowed.";

        $_SESSION['message_type'] =

            "error";


        header("Location: admin.php");

        exit();

    }


    /* =================================
       READ CHAPTER CONTENT
    ================================= */

    $chapter_content = file_get_contents(

        $_FILES['chapter_file']['tmp_name']

    );


    if (

        $chapter_content === false ||

        trim($chapter_content) === ""

    ) {

        $_SESSION['message'] =

            "The chapter file is empty.";

        $_SESSION['message_type'] =

            "warning";


        header("Location: admin.php");

        exit();

    }


    /* =================================
       UPLOAD COVER IMAGE
    ================================= */

    $imageName = "";


    if (

        isset($_FILES['image']) &&

        $_FILES['image']['error']

        === UPLOAD_ERR_OK

    ) {


        $imageExtension = strtolower(

            pathinfo(

                $_FILES['image']['name'],

                PATHINFO_EXTENSION

            )

        );


        $allowedImageExtensions = [

            "jpg",

            "jpeg",

            "png",

            "webp"

        ];


        if (

            !in_array(

                $imageExtension,

                $allowedImageExtensions

            )

        ) {

            $_SESSION['message'] =

                "Invalid cover image format.";

            $_SESSION['message_type'] =

                "error";


            header("Location: admin.php");

            exit();

        }


        $imageName =

            time()

            . "_"

            . uniqid()

            . "."

            . $imageExtension;


        $imagePath =

            "images/" . $imageName;


        if (

            !move_uploaded_file(

                $_FILES['image']['tmp_name'],

                $imagePath

            )

        ) {

            $_SESSION['message'] =

                "Failed to upload cover image.";

            $_SESSION['message_type'] =

                "error";


            header("Location: admin.php");

            exit();

        }

    }


    /* =================================
       START DATABASE TRANSACTION
    ================================= */

    mysqli_begin_transaction($conn);


    try {


        /* =============================
           INSERT NOVEL
        ============================= */

        $stmt = mysqli_prepare(

            $conn,

            "INSERT INTO novels

            (

                author_id,

                title,

                genre,

                access_type,

                image,

                author,

                description

            )

            VALUES (?, ?, ?, ?, ?, ?, ?)"

        );


        if (!$stmt) {

            throw new Exception(

                mysqli_error($conn)

            );

        }


        mysqli_stmt_bind_param(

            $stmt,

            "issssss",

            $user['id'],

            $title,

            $genre,

            $access_type,

            $imageName,

            $author,

            $description

        );


        if (

            !mysqli_stmt_execute($stmt)

        ) {

            throw new Exception(

                mysqli_stmt_error($stmt)

            );

        }


        /* =============================
           GET NEW NOVEL ID
        ============================= */

        $novel_id = mysqli_insert_id($conn);


        mysqli_stmt_close($stmt);


        /* =============================
           INSERT CHAPTER 1
        ============================= */

        $chapter_stmt = mysqli_prepare(

            $conn,

            "INSERT INTO chapters

            (

                novel_id,

                chapter_number,

                chapter_title,

                chapter_content,

                chapter_file,

                access_type

            )

            VALUES (?, ?, ?, ?, ?, ?)"

        );


        if (!$chapter_stmt) {

            throw new Exception(

                mysqli_error($conn)

            );

        }


        mysqli_stmt_bind_param(

            $chapter_stmt,

            "iissss",

            $novel_id,

            $chapter_number,

            $chapter_title,

            $chapter_content,

            $chapter_original_name,

            $chapter_access_type

        );


        if (

            !mysqli_stmt_execute($chapter_stmt)

        ) {

            throw new Exception(

                mysqli_stmt_error($chapter_stmt)

            );

        }


        mysqli_stmt_close($chapter_stmt);


        /* =============================
           COMMIT
        ============================= */

        mysqli_commit($conn);


        $_SESSION['message'] =

            "Novel and Chapter 1 added successfully.";

        $_SESSION['message_type'] =

            "success";


        header("Location: admin.php");

        exit();


    } catch (Exception $e) {


        /* =============================
           ROLLBACK
        ============================= */

        mysqli_rollback($conn);


        /* DELETE IMAGE IF UPLOADED */

        if (

            !empty($imageName) &&

            file_exists(

                "images/" . $imageName

            )

        ) {

            unlink(

                "images/" . $imageName

            );

        }


        $_SESSION['message'] =

            "Failed to add novel and chapter: "

            . $e->getMessage();

        $_SESSION['message_type'] =

            "error";


        header("Location: admin.php");

        exit();

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Add Novel</title>

</head>

<body>

<p>

This page processes the Add Novel and Chapter form.

</p>

</body>

</html>