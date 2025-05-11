<?php 
session_start();
include "connect.php";

require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = ""; // Initialize the error message variable

if (isset($_POST['login'])) {
    $student_id = isset($_POST["student_id"]) ? strip_tags($_POST["student_id"]) : "";
    $password = isset($_POST["password"]) ? strip_tags($_POST["password"]) : "";

    if ($student_id == "" || $password == "") {
        $error_message = "Student ID and Password are required";
    } else {
        $query = $db->prepare("SELECT * FROM accounts WHERE student_id = ? AND password = ?");
        $query->execute(array($student_id, $password));
        $control = $query->fetch(PDO::FETCH_OBJ);

        if ($control) {
            $_SESSION['user_id'] = $control->student_id;
            $_SESSION['student_id'] = $control->student_id;
            $_SESSION["role"] = $control->role;
        
            if ($control->role == "electoral_manager" || $control->role == "admin") {
                if (!empty($control->email)) {
                    $_SESSION['pending_2fa'] = true;
                    $otp = rand(100000, 999999);
                    $first_name = isset($control->firstname) ? $control->firstname : 'User';
                    $_SESSION['2fa_otp'] = $otp;
                    $_SESSION['2fa_otp_expiry'] = time() + 300; // 5 minutes

                    // Send OTP to user's email using PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'jhongarapan@gmail.com'; // your Gmail address
                        $mail->Password = 'degy ppco ofxr kutl';    // your Gmail App Password
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('your_gmail@gmail.com', 'UniVote');
                        $mail->addAddress($control->email);

                        $mail->setFrom('your_gmail@gmail.com', 'UniVote');
                        $mail->addAddress(address: $user['email']);

                        $mail->isHTML(true);
                        $mail->Subject = 'UniVote Verification Code';
                        $mail->Body = "Hello {$first_name},<br><br>
                            Your UniVote verification code is: <b>{$otp}</b><br><br>
                            This code will expire in <i>{$expires_in} minutes</i>.<br><br>
                            &#9888; If you did not request this code, please ignore this email or contact your administrator immediately.<br><br>
                            Thank you,<br>
                            UniVote Team";

                        $mail->send();
                    } catch (Exception $e) {
                        $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                    // Redirect to 2FA page
                    header("Location: 2fa.php");
                    exit();
                } else {
                    $error_message = "No email address found for this account. Please contact the administrator.";
                }
            } else if ($control->role == "voter") {
                $_SESSION['pending_2fa_idscan'] = true;
                $_SESSION['2fa_student_id'] = $control->student_id;
                // Combine firstname and lastname for full name (make sure both exist)
                $firstname = isset($control->firstname) ? $control->firstname : '';
                $lastname = isset($control->lastname) ? $control->lastname : '';
                $_SESSION['2fa_fullname'] = trim($firstname . ' ' . $lastname);
                header("Location: 2fa_idscan.php");
                exit();
        } else {
            $login_error_message = "User ID or Password is incorrect";
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
    .modal { position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:9999; }
    .modal-content { background:#fff; padding:32px 24px; border-radius:10px; box-shadow:0 4px 24px rgba(0,0,0,0.12); min-width:320px; }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="login-container shadow p-4 bg-white rounded">
        <img class="logo mb-3" src="../../Assets/Image/logo-orig-green.png" alt="UniVote White Logo">
        <h2 class="mb-4">Login</h2>
        <form id="loginForm" method="POST">
            <div class="mb-3">
                <label for="student_id" class="form-label fw-bold">User ID:</label>
                <input type="text" class="form-control" name="student_id" id="student_id" placeholder="YY-XXXXX" required>
            </div>
            <div class="mb-3 input-group">
                <label for="password" class="form-label fw-bold">Password:</label>
                <div class="password-container">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Show password">
                        <span class="eye-open">
                            <!-- Eye open SVG -->
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="12" rx="9" ry="5"/>
                                <circle cx="12" cy="12" r="2"/>
                            </svg>
                        </span>
                        <span class="eye-closed" style="display:none;">
                            <!-- Eye closed SVG (open eye with slash) -->
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="12" rx="9" ry="5"/>
                                <circle cx="12" cy="12" r="2"/>
                                <line x1="4" y1="20" x2="20" y2="4" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="remember_me" id="rememberMe">
                <label class="form-check-label fw-bold" for="rememberMe">Remember Me</label>
            </div>
            <?php if (!empty($login_error_message)): ?>
                <div class="alert alert-danger text-center py-2"><?php echo $login_error_message; ?></div>
            <?php endif; ?>
            <button type="submit" name="login" class="btn btn-success w-100">Login</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle CDN (for optional Bootstrap JS features) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>