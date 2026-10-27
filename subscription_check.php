<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

include("connect.php");

if(isset($_SESSION['email'])){

    $email = $_SESSION['email'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT subscription,
                subscription_expiry
         FROM users
         WHERE email=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $email
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if($user = mysqli_fetch_assoc($result)){

        if(
            !empty($user['subscription_expiry']) &&
            strtotime($user['subscription_expiry']) < time()
        ){

            $stmt = mysqli_prepare(

                $conn,

                "UPDATE users
                 SET
                 subscription='Free',
                 transaction_id=NULL,
                 subscription_date=NULL,
                 subscription_expiry=NULL
                 WHERE email=?"

            );

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );

            mysqli_stmt_execute($stmt);

        }

    }

}
?>
