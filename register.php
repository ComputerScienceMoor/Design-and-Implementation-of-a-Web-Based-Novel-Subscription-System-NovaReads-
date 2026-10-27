<?php

session_start();
include("connect.php");

/* =====================================
   REGISTER USER
===================================== */

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    /* Get selected account type */
    $role = isset($_POST['role']) ? $_POST['role'] : "user";

    /* Only allow valid roles */
    if ($role !== "author") {
        $role = "user";
    }

    /* Validate Inputs */

    if (
        empty($fullname) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['message_type'] = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['message'] = "Invalid email address.";
        $_SESSION['message_type'] = "error";

    }

    elseif (strlen($password) < 6) {

        $_SESSION['message'] = "Password must be at least 6 characters.";
        $_SESSION['message_type'] = "error";

    }

    elseif ($password != $confirm_password) {

        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['message_type'] = "error";

    }

    else {

        /* =====================================
           CHECK EXISTING EMAIL
        ===================================== */

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        if (!$check) {

            $_SESSION['message'] = "Unable to check account.";
            $_SESSION['message_type'] = "error";

        } else {

            /* FIX: supply email to the ? placeholder */

            mysqli_stmt_bind_param(
                $check,
                "s",
                $email
            );

            mysqli_stmt_execute($check);

            $result = mysqli_stmt_get_result($check);

            if (mysqli_num_rows($result) > 0) {

                $_SESSION['message'] = "Email already exists.";
                $_SESSION['message_type'] = "warning";

            }

            else {

                /* =====================================
                   HASH PASSWORD
                ===================================== */

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /* =====================================
                   INSERT NEW USER
                ===================================== */

                $insert = mysqli_prepare(

                    $conn,

                    "INSERT INTO users
                    (
                        fullname,
                        email,
                        password,
                        subscription,
                        role
                    )
                    VALUES
                    (?, ?, ?, 'Free', ?)"

                );

                if (!$insert) {

                    $_SESSION['message'] =
                        "Unable to create account.";

                    $_SESSION['message_type'] =
                        "error";

                } else {

                    mysqli_stmt_bind_param(

                        $insert,

                        "ssss",

                        $fullname,
                        $email,
                        $hashed_password,
                        $role

                    );

                    if (mysqli_stmt_execute($insert)) {

                        $_SESSION['message'] =
                            "Registration Successful. Login to continue.";

                        $_SESSION['message_type'] =
                            "success";

                        header("Location: login.php");

                        exit();

                    }

                    else {

                        $_SESSION['message'] =
                            "Registration failed.";

                        $_SESSION['message_type'] =
                            "error";

                    }

                    mysqli_stmt_close($insert);
                }
            }

            mysqli_stmt_close($check);
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

<title>Register</title>

<link rel="stylesheet"
href="novel.css">

<script src="novel.js"></script>

</head>

<body>

<?php include("toast.php"); ?>

<div class="auth-page">

<div class="form-container">

<h1>
Create Account
</h1>

<p>
Join NovaReads today.
</p>

<form
method="POST"
onsubmit="return validateRegister();"
>

<label>
Full Name
</label>

<input
type="text"
name="fullname"
id="fullname"
required>

<label>
Email
</label>

<input
type="email"
name="email"
id="registerEmail"
required>

<label>
Password
</label>

<input
type="password"
id="registerPassword"
name="password"
onkeyup="checkPasswordStrength();checkPasswordMatch();"
required>

<label>
Confirm Password
</label>

<input
type="password"
id="confirmPassword"
name="confirm_password"
onkeyup="checkPasswordMatch();"
required>

<p id="passwordStrength"></p>

<p id="passwordMessage"></p>

<label>
Account Type
</label>

<select
name="role"
required
>

<option value="user">
Reader
</option>

<option value="author">
Author
</option>

</select>

<button
type="submit"
name="register"
>
Create Account
</button>

</form>

<br>

<p>
Already have an account?

<a href="login.php">
Login Here
</a>

</p>

</div>

</div>

</body>

</html>