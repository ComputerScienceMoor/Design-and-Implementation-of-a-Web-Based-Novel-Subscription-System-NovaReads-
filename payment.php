<?php

session_start();

include("connect.php");
include("flutterwave_config.php");

/* ==========================
   LOGIN CHECK
========================== */

if(!isset($_SESSION['email'])){

    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

/* ==========================
   VALIDATE PLAN
========================== */

if(!isset($_GET['plan'])){

    header("Location: subscribe.php");
    exit();
}

$plan = $_GET['plan'];

$plans = [

    "Premium" => 2000,

    "VIP" => 5000

];

if(!isset($plans[$plan])){

    header("Location: subscribe.php");
    exit();
}

$amount = $plans[$plan];

/* ==========================
   GET USER
========================== */

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

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

$tx_ref = uniqid("NOVA_");

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Checkout</title>

<link rel="stylesheet" href="novel.css">

<script src="https://checkout.flutterwave.com/v3.js"></script>

</head>

<body>

<div class="auth-page">

<div class="form-container">

<h2>

Flutterwave Checkout

</h2>

<p>

Plan:
<strong>

<?php echo $plan; ?>

</strong>

</p>

<p>

Amount:
<strong>

₦<?php echo number_format($amount); ?>

</strong>

</p>

<br>

<button onclick="makePayment()">

Pay with Flutterwave

</button>

<br><br>

<a href="subscribe.php">

<button>

Cancel

</button>

</a>

</div>

</div>

<script>

function makePayment(){

FlutterwaveCheckout({

public_key:
"<?php echo FLW_PUBLIC_KEY; ?>",

tx_ref:
"<?php echo $tx_ref; ?>",

amount:
<?php echo $amount; ?>,

currency:
"NGN",

payment_options:
"card,banktransfer,ussd",

customer:{

email:
"<?php echo $user['email']; ?>",

name:
"<?php echo htmlspecialchars($user['fullname']); ?>"

},

customizations:{

title:
"NovaReads",

description:
"Subscription Payment",

logo:
""

},

callback:function(data){

window.location.href=

"verify_payment.php"

+

"?transaction_id="

+

data.transaction_id

+

"&plan=<?php echo $plan; ?>";

},

onclose:function(){

alert("Payment cancelled.");

}

});

}

</script>

</body>

</html>
