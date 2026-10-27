<?php

session_start();
include("connect.php");

/* =====================================
   LOGIN USER
===================================== */

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){

        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['message_type'] = "error";

    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $_SESSION['message'] = "Invalid email address.";
        $_SESSION['message_type'] = "error";

    }else{

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

        if(mysqli_num_rows($result) == 1){

            $user = mysqli_fetch_assoc($result);

            if(password_verify($password, $user['password'])){

                session_regenerate_id(true);

                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                $_SESSION['message'] = "Welcome back, ".$user['fullname']."!";
                $_SESSION['message_type'] = "success";

                if ($user['role'] === "author") {

                    header("Location: author_dashboard.php");

                } elseif ($user['role'] === "admin") {

                    header("Location: admin.php");

                } else {

                    header("Location: dashboard.php");

                }

                exit();

            }else{

                $_SESSION['message'] = "Incorrect password.";
                $_SESSION['message_type'] = "error";

            }

        }else{

            $_SESSION['message'] = "No account found with that email.";
            $_SESSION['message_type'] = "error";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Login

</title>

<link rel="stylesheet"
href="novel.css">

<script src="novel.js"></script>

</head>

<body>

<?php include("toast.php"); ?>

<div class="auth-page">

<div class="form-container">

<div class="auth-logo">

<span class="logo">NovaReads</span>

</div>

<h2>

Welcome Back

</h2>

<p>

Login to continue reading your favourite novels.

</p>

<form

method="POST"

onsubmit="return validateLogin();"

>

<label>

Email

</label>

<input

type="email"

name="email"

id="loginEmail"

required>

<label>

Password

</label>

<input

type="password"

name="password"

id="loginPassword"

required>

<button

type="submit"

name="login"

>

Login

</button>

</form>

<br>

<p class="auth-footer">

Don't have an account?

<a href="register.php">

Register Here

</a>

</p>

</div>

</div>

</body>

</html>
