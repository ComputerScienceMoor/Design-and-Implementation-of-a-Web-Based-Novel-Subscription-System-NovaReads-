/* =====================================
   LOGIN VALIDATION
===================================== */

function validateLogin() {

    const email =
    document.getElementById("loginEmail").value.trim();

    const password =
    document.getElementById("loginPassword").value.trim();

    if(email === "" || password === ""){

        alert("Please fill in all login fields.");

        return false;
    }

    if(!validateEmail(email)){

        alert("Please enter a valid email address.");

        return false;
    }

    return true;
}

/* =====================================
   REGISTER VALIDATION
===================================== */

function validateRegister(){

    const fullname =
    document.getElementById("fullname").value.trim();

    const email =
    document.getElementById("registerEmail").value.trim();

    const password =
    document.getElementById("registerPassword").value;

    const confirmPassword =
    document.getElementById("confirmPassword").value;

    if(
        fullname === "" ||
        email === "" ||
        password === "" ||
        confirmPassword === ""
    ){

        alert("All fields are required.");

        return false;
    }

    if(fullname.length < 3){

        alert("Full name must contain at least 3 characters.");

        return false;
    }

    if(!validateEmail(email)){

        alert("Please enter a valid email address.");

        return false;
    }

    if(password.length < 6){

        alert("Password must be at least 6 characters.");

        return false;
    }

    if(password !== confirmPassword){

        alert("Passwords do not match.");

        return false;
    }

    return true;
}

/* =====================================
   EMAIL VALIDATION
===================================== */

function validateEmail(email){

    const pattern =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return pattern.test(email);
}

/* =====================================
   LIVE PASSWORD CHECK
===================================== */

function checkPasswordMatch(){

    const password =
    document.getElementById("registerPassword");

    const confirm =
    document.getElementById("confirmPassword");

    const message =
    document.getElementById("passwordMessage");

    if(
        !password ||
        !confirm ||
        !message
    ){
        return;
    }

    if(confirm.value === ""){

        message.innerHTML = "";

        return;
    }

    if(password.value === confirm.value){

        message.innerHTML =
        "✔ Passwords match";

        message.style.color =
        "limegreen";

    }else{

        message.innerHTML =
        "✖ Passwords do not match";

        message.style.color =
        "red";
    }
}

/* =====================================
   PASSWORD STRENGTH
===================================== */

function checkPasswordStrength(){

    const password =
    document.getElementById("registerPassword");

    const strength =
    document.getElementById("passwordStrength");

    if(
        !password ||
        !strength
    ){
        return;
    }

    if(password.value.length < 6){

        strength.innerHTML =
        "Weak Password";

        strength.style.color =
        "red";

    }else if(password.value.length < 10){

        strength.innerHTML =
        "Medium Password";

        strength.style.color =
        "orange";

    }else{

        strength.innerHTML =
        "Strong Password";

        strength.style.color =
        "limegreen";
    }
}