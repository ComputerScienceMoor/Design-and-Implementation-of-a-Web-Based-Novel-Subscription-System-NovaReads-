<?php

if (
    isset($_SESSION['message']) &&
    !empty($_SESSION['message'])
) {

$type = isset($_SESSION['message_type'])
        ? $_SESSION['message_type']
        : "info";

?>

<div id="toast"
class="toast toast-<?php echo htmlspecialchars($type); ?>">

<span>

<?php echo htmlspecialchars($_SESSION['message']); ?>

</span>

<button
class="toast-close"
onclick="closeToast()">

&times;

</button>

</div>

<script>

function closeToast(){

const toast =
document.getElementById("toast");

if(toast){

toast.classList.add("hide");

setTimeout(function(){

toast.remove();

},500);

}

}

setTimeout(function(){

closeToast();

},3000);

</script>

<?php

unset($_SESSION['message']);
unset($_SESSION['message_type']);

}

?>