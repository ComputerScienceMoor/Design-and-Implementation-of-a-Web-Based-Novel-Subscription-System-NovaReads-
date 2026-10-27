<?php

session_start();

include("connect.php");
include("flutterwave_config.php");

/* =====================================
   LOGIN CHECK
===================================== */

if (!isset($_SESSION['email'])) {

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* =====================================
   VALIDATE PARAMETERS
===================================== */

if (
    !isset($_GET['transaction_id']) ||
    !isset($_GET['plan'])
) {

    $_SESSION['message'] = "Invalid payment request.";
    $_SESSION['message_type'] = "error";

    header("Location: subscribe.php");
    exit();
}

$transaction_id = (int)$_GET['transaction_id'];
$plan = $_GET['plan'];

$allowedPlans = ["Premium", "VIP"];

if (!in_array($plan, $allowedPlans)) {

    $_SESSION['message'] = "Invalid subscription plan.";
    $_SESSION['message_type'] = "error";

    header("Location: subscribe.php");
    exit();
}

/* =====================================
   GET USER
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT fullname,email
     FROM users
     WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$userResult = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($userResult);

if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit();
}

/* =====================================
   VERIFY PAYMENT WITH FLUTTERWAVE
===================================== */

$url = "https://api.flutterwave.com/v3/transactions/" .
       $transaction_id .
       "/verify";

$curl = curl_init();

curl_setopt_array($curl, [

    CURLOPT_URL => $url,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [

        "Authorization: Bearer " . FLW_SECRET_KEY,

        "Content-Type: application/json"

    ]

]);

$response = curl_exec($curl);

curl_close($curl);

$result = json_decode($response, true);

/* =====================================
   PAYMENT SUCCESS
===================================== */

if (

    isset($result['status']) &&
    $result['status'] == "success" &&

    isset($result['data']['status']) &&
    $result['data']['status'] == "successful"

) {

    $subscriptionDate = date("Y-m-d H:i:s");

    $subscriptionExpiry = date(
        "Y-m-d H:i:s",
        strtotime("+30 days")
    );

    /* UPDATE USER */

    $stmt = mysqli_prepare(

        $conn,

        "UPDATE users

         SET

         subscription=?,
         transaction_id=?,
         subscription_date=?,
         subscription_expiry=?

         WHERE email=?"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "sssss",

        $plan,
        $transaction_id,
        $subscriptionDate,
        $subscriptionExpiry,
        $email

    );

    mysqli_stmt_execute($stmt);

    /* SAVE PAYMENT HISTORY */

    $amount = ($plan == "Premium") ? 2000 : 5000;

    $paymentMethod = isset($result['data']['payment_type'])
        ? $result['data']['payment_type']
        : "Unknown";

    $status = "Successful";

    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO payments
        (
            user_email,
            fullname,
            plan,
            amount,
            transaction_id,
            payment_status,
            payment_method
        )

        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?
        )"

    );

    mysqli_stmt_bind_param(

        $stmt,

        "sssdsss",

        $email,
        $user['fullname'],
        $plan,
        $amount,
        $transaction_id,
        $status,
        $paymentMethod

    );

    mysqli_stmt_execute($stmt);

    $_SESSION['message'] =
    "Payment successful! Your subscription has been activated.";

    $_SESSION['message_type'] =
    "success";

    header("Location: dashboard.php");
    exit();

}

/* =====================================
   PAYMENT FAILED
===================================== */

$_SESSION['message'] =
"Payment verification failed.";

$_SESSION['message_type'] =
"error";

header("Location: subscribe.php");
exit();

?>