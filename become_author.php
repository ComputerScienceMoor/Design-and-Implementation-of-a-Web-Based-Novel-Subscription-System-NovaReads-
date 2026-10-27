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

$message = "";

$message_type = "";


/* =====================================
   GET CURRENT USER
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT * FROM users WHERE id = ?"

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $user_id

);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit();

}


/* =====================================
   CHECK CURRENT ROLE
===================================== */

if ($user['role'] === "author") {

    header("Location: author_dashboard.php");

    exit();

}


/* =====================================
   CHECK EXISTING APPLICATION
===================================== */

$stmt = mysqli_prepare(

    $conn,

    "SELECT * FROM author_applications
     WHERE user_id = ?
     LIMIT 1"

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $user_id

);

mysqli_stmt_execute($stmt);

$application_result = mysqli_stmt_get_result($stmt);

$application = mysqli_fetch_assoc($application_result);


/* =====================================
   SUBMIT APPLICATION
===================================== */

if (isset($_POST['submit_application'])) {


    $reason = trim($_POST['reason']);


    if (empty($reason)) {

        $message = "Please explain why you want to become an author.";

        $message_type = "error";

    }

    elseif (strlen($reason) < 20) {

        $message = "Please provide a little more information.";

        $message_type = "error";

    }

    elseif ($application) {

        $message = "You have already submitted an author application.";

        $message_type = "error";

    }

    else {


        $stmt = mysqli_prepare(

            $conn,

            "INSERT INTO author_applications
             (user_id, reason, status)
             VALUES (?, ?, 'pending')"

        );


        mysqli_stmt_bind_param(

            $stmt,

            "is",

            $user_id,

            $reason

        );


        if (mysqli_stmt_execute($stmt)) {

            $message =
            "Your author application has been submitted successfully.";

            $message_type = "success";


            /* Refresh application information */

            $application = [

                "reason" => $reason,

                "status" => "pending"

            ];

        }

        else {

            $message =
            "Something went wrong. Please try again.";

            $message_type = "error";

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

content="width=device-width, initial-scale=1.0">

<title>

Become an Author - NovaReads

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

<a href="dashboard.php">

Dashboard

</a>

<a href="favorites.php">

Favorites

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


<main class="auth-page">


<div class="form-container">


<div class="auth-logo">

<span class="logo">

NovaReads

</span>

</div>


<h2>

Become an Author

</h2>


<p>

Share your stories and publish your novels on NovaReads.

</p>


<?php if ($message != "") { ?>

<div class="form-message <?php echo $message_type; ?>">

<?php echo htmlspecialchars($message); ?>

</div>

<?php } ?>


<?php if ($application) { ?>


<div class="application-status">


<h3>

Application Status

</h3>


<p>

<strong>

Status:

</strong>


<span class="status-<?php echo strtolower($application['status']); ?>">

<?php echo htmlspecialchars($application['status']); ?>

</span>

</p>


<p>

<strong>

Your Reason:

</strong>

</p>


<p>

<?php echo nl2br(

htmlspecialchars(

$application['reason']

)

); ?>

</p>


<?php if ($application['status'] === "pending") { ?>

<p>

Your application is currently waiting for admin review.

</p>

<?php } ?>


<?php if ($application['status'] === "approved") { ?>

<p>

Congratulations! You can now upload novels.

</p>

<a href="author_dashboard.php">

<button>

Go to Author Dashboard

</button>

</a>

<?php } ?>


<?php if ($application['status'] === "rejected") { ?>

<p>

Your application was not approved at this time.

</p>

<?php } ?>


</div>


<?php } else { ?>


<form

method="POST"

action="become_author.php">

<label>

Full Name

</label>


<input

type="text"

value="<?php echo htmlspecialchars($user['fullname']); ?>"

disabled>


<label>

Email

</label>


<input

type="email"

value="<?php echo htmlspecialchars($user['email']); ?>"

disabled>


<label>

Why do you want to become an author?

</label>


<textarea

name="reason"

rows="8"

placeholder="Tell us about yourself and the type of novels you want to publish..."

required></textarea>


<button

type="submit"

name="submit_application">

Submit Application

</button>


</form>


<?php } ?>


<br>


<p class="auth-footer">

<a href="dashboard.php">

← Back to Dashboard

</a>

</p>


</div>


</main>


</body>

</html>