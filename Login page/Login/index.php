<?php
session_start();
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"])) {
    header("location: login.php");
    exit();
}

// Set redirect URL based on user role
switch ($_SESSION["role"]) {
    case "admin":
        $redirect_url = "../../Admin Page/Homepage/index.php";
        break;
    case "electoral_manager":
        $redirect_url = "../../EM Page/Homepage/index.php";
        break;
    case "voter":
        $redirect_url = "../../Voters Page/Homepage (Php)/index.php";
        break;
    default:
        $redirect_url = "login.php";
        break;
}

header("Refresh:2; url=$redirect_url");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container shadow p-4 bg-white rounded text-center">
            <center><h1 class="text-danger mb-3">Access Denied</h1></center>
            <p>You do not have permission to access this page.<br>
            <i>Wala ka karapatan na pumunta sa page na ito </i><br>
            <hr>
            Redirecting you back to your homepage...</p>

            <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn btn-primary mt-3">Go Back Now</a>
        </div>
    </div>

    
</body>
</html>