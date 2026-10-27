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
   GET USER
========================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if(!$user){

    session_destroy();

    header("Location: login.php");

    exit();
}

/* ==========================
   UPDATE PROFILE
========================== */

if(isset($_POST['update'])){

    $fullname = trim($_POST['fullname']);

    $password = trim($_POST['new_password']);

    if(empty($fullname)){

        $_SESSION['message'] = "Full name cannot be empty.";

        $_SESSION['message_type'] = "error";

    }else{

        if(empty($password)){

            $stmt = mysqli_prepare(

                $conn,

                "UPDATE users
                 SET fullname=?
                 WHERE email=?"

            );

            mysqli_stmt_bind_param(

                $stmt,

                "ss",

                $fullname,

                $email

            );

        }else{

            if(strlen($password) < 6){

                $_SESSION['message'] =
                "Password must be at least 6 characters.";

                $_SESSION['message_type'] =
                "warning";

                header("Location: edit_profile.php");

                exit();

            }

            $hashedPassword = password_hash(

                $password,

                PASSWORD_DEFAULT

            );

            $stmt = mysqli_prepare(

                $conn,

                "UPDATE users
                 SET fullname=?,
                 password=?
                 WHERE email=?"

            );

            mysqli_stmt_bind_param(

                $stmt,

                "sss",

                $fullname,

                $hashedPassword,

                $email

            );

        }

        if(mysqli_stmt_execute($stmt)){

            $_SESSION['message'] =
            "Profile updated successfully.";

            $_SESSION['message_type'] =
            "success";

        }else{

            $_SESSION['message'] =
            "Unable to update profile.";

            $_SESSION['message_type'] =
            "error";

        }

        header("Location: profile.php");

        exit();

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

Edit Profile

</title>

<link
rel="stylesheet"
href="novel.css">

</head>

<body>

<?php include("toast.php"); ?>

<nav>

<h1 class="logo">

NovaReads

</h1>

<div class="nav-links">

<a href="dashboard.php">

Dashboard

</a>

<a href="profile.php">

Profile

</a>

<a href="logout.php">

Logout

</a>

</div>

</nav>

<div class="hero-dashboard">

<div class="hero-overlay">

<h1>

Edit Profile

</h1>

<p>

Update your account information.

</p>

</div>

</div>

<section class="dashboard">

<div class="form-container">
    <h2>

Account Settings

</h2>

<form method="POST">

<label>

Full Name

</label>

<input

type="text"

name="fullname"

value="<?php echo htmlspecialchars($user['fullname']); ?>"

required>

<label>

Email Address

</label>

<input

type="email"

value="<?php echo htmlspecialchars($user['email']); ?>"

disabled>

<label>

New Password

</label>

<input

type="password"

name="new_password"

placeholder="Leave blank to keep current password">

<br><br>

<button

type="submit"

name="update">

Save Changes

</button>

</form>

<br>

<a href="profile.php">

<button>

← Back to Profile

</button>

</a>

</div>

</section>

<footer class="footer">

<p>

&copy; <?php echo date("Y"); ?>

NovaReads

</p>

</footer>

</body>

</html>