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
   CHECK ADMIN
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT role FROM users WHERE id = ?"

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $user_id

);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


if (!$user || $user['role'] !== "admin") {

    header("Location: dashboard.php");

    exit();

}


/* =====================================
   APPROVE APPLICATION
===================================== */

if (isset($_GET['approve'])) {


    $application_id = (int) $_GET['approve'];


    /* GET USER ID */

    $stmt = mysqli_prepare(

        $conn,

        "SELECT user_id
         FROM author_applications
         WHERE id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "i",

        $application_id

    );


    mysqli_stmt_execute($stmt);


    $result = mysqli_stmt_get_result($stmt);

    $application = mysqli_fetch_assoc($result);


    if ($application) {


        $applicant_id = $application['user_id'];


        /* UPDATE USER ROLE */

        $update_user = mysqli_prepare(

            $conn,

            "UPDATE users
             SET role = 'author'
             WHERE id = ?"

        );


        mysqli_stmt_bind_param(

            $update_user,

            "i",

            $applicant_id

        );


        mysqli_stmt_execute($update_user);

        mysqli_stmt_close($update_user);


        /* UPDATE APPLICATION */

        $update_application = mysqli_prepare(

            $conn,

            "UPDATE author_applications
             SET status = 'approved',
                 reviewed_at = NOW()
             WHERE id = ?"

        );


        mysqli_stmt_bind_param(

            $update_application,

            "i",

            $application_id

        );


        mysqli_stmt_execute($update_application);

        mysqli_stmt_close($update_application);


        $_SESSION['message'] =

            "Author application approved successfully.";

        $_SESSION['message_type'] = "success";

    }


    header("Location: admin_applications.php");

    exit();

}


/* =====================================
   REJECT APPLICATION
===================================== */

if (isset($_GET['reject'])) {


    $application_id = (int) $_GET['reject'];


    $stmt = mysqli_prepare(

        $conn,

        "UPDATE author_applications
         SET status = 'rejected',
             reviewed_at = NOW()
         WHERE id = ?"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "i",

        $application_id

    );


    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    $_SESSION['message'] =

        "Author application rejected.";

    $_SESSION['message_type'] = "error";


    header("Location: admin_applications.php");

    exit();

}


/* =====================================
   GET APPLICATIONS
===================================== */

$query = mysqli_query(

    $conn,

    "SELECT

        author_applications.id,

        author_applications.reason,

        author_applications.status,

        author_applications.created_at,

        author_applications.reviewed_at,

        users.fullname,

        users.email

     FROM author_applications

     INNER JOIN users

     ON author_applications.user_id = users.id

     ORDER BY author_applications.created_at DESC"

);


?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"

content="width=device-width, initial-scale=1.0">

<title>

Author Applications | NovaReads

</title>

<link

rel="stylesheet"

href="novel.css">

</head>

<body>


<?php include("toast.php"); ?>


<header>

<nav>

<h1 class="logo">

NovaReads

</h1>


<div class="nav-links">

<a href="admin.php">

Admin Dashboard

</a>


<a href="dashboard.php">

Home

</a>


<a href="logout.php">

Logout

</a>

</div>

</nav>

</header>


<main class="dashboard">


<h2 class="section-title">

Author Applications

</h2>


<div class="application-container">


<?php if ($query && mysqli_num_rows($query) > 0) { ?>


<?php while ($application = mysqli_fetch_assoc($query)) { ?>


<div class="application-card">


<h3>

<?php echo htmlspecialchars(

$application['fullname']

); ?>

</h3>


<p>

<strong>

Email:

</strong>


<?php echo htmlspecialchars(

$application['email']

); ?>

</p>


<p>

<strong>

Reason:

</strong>

</p>


<p>

<?php echo nl2br(

htmlspecialchars(

$application['reason']

)

); ?>

</p>


<p>

<strong>

Status:

</strong>


<span class="status-<?php echo strtolower(

$application['status']

); ?>">


<?php echo htmlspecialchars(

$application['status']

); ?>


</span>

</p>


<p>

<strong>

Applied:

</strong>


<?php echo htmlspecialchars(

$application['created_at']

); ?>

</p>


<?php if (

$application['status'] === "pending"

) { ?>


<div class="application-actions">


<a

href="admin_applications.php?approve=<?php echo $application['id']; ?>"

onclick="return confirm('Approve this author application?');"

>


<button class="hero-btn">

Approve

</button>


</a>


<a

href="admin_applications.php?reject=<?php echo $application['id']; ?>"

onclick="return confirm('Reject this author application?');"

>


<button class="premium-btn">

Reject

</button>


</a>


</div>


<?php } else { ?>


<p>

This application has already been reviewed.

</p>


<?php } ?>


</div>


<?php } ?>


<?php } else { ?>


<div class="empty-state">


<h2>

No Author Applications

</h2>


<p>

There are currently no author applications.

</p>


</div>


<?php } ?>


</div>


</main>


</body>

</html>