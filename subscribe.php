<?php

session_start();
include("connect.php");
include("subscription_check.php");
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
    "SELECT fullname, subscription
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

$user = mysqli_fetch_assoc($result);

$currentPlan = empty($user['subscription'])
    ? "Free"
    : $user['subscription'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

Subscription Plans

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

<a href="favorites.php">
Favorites
</a>

<a href="logout.php">
Logout
</a>

</div>

</nav>

<div class="hero-dashboard">

<div class="hero-overlay">

<h1>

Choose Your Subscription

</h1>

<p>

Unlock premium novels and exclusive chapters.

</p>

</div>

</div>

<div style="text-align:center;margin:20px;">

<h3>

Current Plan:

<strong>

<?php echo htmlspecialchars($currentPlan); ?>

</strong>

</h3>

</div>

<section class="subscription-section">

<!-- FREE -->

<div class="plan-card">

<h2>

Free

</h2>

<p>

✔ Limited novels

</p>

<p>

✔ Basic reading

</p>

<h3>

₦0

</h3>

<?php

if($currentPlan=="Free"){

?>

<button disabled>

Current Plan

</button>

<?php

}else{

?>

<a href="subscribe_process.php?plan=Free">

<button>

Switch To Free

</button>

</a>

<?php

}

?>

</div>

<!-- PREMIUM -->

<div class="plan-card">

<h2>

Premium

</h2>

<p>

✔ Unlimited novels

</p>

<p>

✔ Premium chapters

</p>

<h3>

₦2,000

</h3>

<?php

if($currentPlan=="Premium"){

?>

<button disabled>

Current Plan

</button>

<?php

}else{

?>

<a href="payment.php?plan=Premium">

<button>

Upgrade

</button>

</a>

<?php

}

?>

</div>

<!-- VIP -->

<div class="plan-card">

<h2>

VIP

</h2>

<p>

✔ Everything in Premium

</p>

<p>

✔ Early access

</p>

<p>

✔ Exclusive releases

</p>

<h3>

₦5,000

</h3>

<?php

if($currentPlan=="VIP"){

?>

<button disabled>

Current Plan

</button>

<?php

}else{

?>

<a href="payment.php?plan=VIP">

<button>

Upgrade

</button>

</a>

<?php

}

?>

</div>

</section>

</body>

</html>
