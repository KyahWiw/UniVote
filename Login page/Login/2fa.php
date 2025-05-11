<?php
session_start();
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = "";

// Fetch user info for email and first name
include "connect.php";
$student_id = $_SESSION['student_id'];
$query = $db->prepare("SELECT email, firstname FROM accounts WHERE student_id = ?");
$query->execute([$student_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Send OTP automatically on first GET (not POST) visit
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($user && !empty($user['email'])) {
        $otp = rand(100000, 999999);
        $_SESSION['2fa_otp'] = $otp;
        $_SESSION['2fa_otp_expiry'] = time() + 300; // 5 minutes
        $_SESSION['pending_2fa'] = true;
        $first_name = isset($user['firstname']) ? $user['firstname'] : 'User';
        $expires_in = ceil(($_SESSION['2fa_otp_expiry'] - time()) / 60);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'au.univote@gmail.com'; // your Gmail address
            $mail->Password = 'mxum czns mqut ahyv';    // your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_gmail@gmail.com', 'UniVote');
            $mail->addAddress($user['email']);

            $mail->isHTML(true);
            $mail->Subject = 'UniVote Verification Code';
            $mail->Body = "Hello {$first_name},<br><br>
                Your UniVote verification code is: <b>{$otp}</b><br><br>
                This code will expire in <i>{$expires_in} minutes</i>.<br><br>
                &#9888; If you did not request this code, please ignore this email or contact your administrator immediately.<br><br>
                Thank you,<br>
                UniVote Team";

            $mail->send();
            $error_message = "A code has been sent to your email.";
        } catch (Exception $e) {
            $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

// Handle 2FA verification
if (isset($_POST['verify_2fa'])) {
    $input_otp = $_POST['otp'];
    if (
        isset($_SESSION['2fa_otp'], $_SESSION['2fa_otp_expiry']) &&
        $_SESSION['2fa_otp'] == $input_otp &&
        time() < $_SESSION['2fa_otp_expiry']
    ) {
        unset($_SESSION['pending_2fa'], $_SESSION['2fa_otp'], $_SESSION['2fa_otp_expiry']);
        $_SESSION['2fa_verified'] = true;

        // Redirect based on role
        if ($_SESSION['role'] == "admin") {
            header("Location: ../../Admin Page/Homepage/index.php");
        } else if ($_SESSION['role'] == "electoral_manager") {
            header("Location: ../../EM Page/Homepage/index.php");
        } else {
            header("Location: ../../Voters Page/Homepage (Php)/index.php");
        }
        exit();
    } else {
        $error_message = "Invalid or expired code.";
    }
}

// Handle resend
if (isset($_POST['resend'])) {
    if ($user && !empty($user['email'])) {
        $otp = rand(100000, 999999);
        $_SESSION['2fa_otp'] = $otp;
        $_SESSION['2fa_otp_expiry'] = time() + 300; // 5 minutes
        $_SESSION['pending_2fa'] = true;
        $first_name = isset($user['firstname']) ? $user['firstname'] : 'User';
        $expires_in = ceil(($_SESSION['2fa_otp_expiry'] - time()) / 60);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'au.univote@gmail.com'; // your Gmail address
            $mail->Password = 'mxum czns mqut ahyv';    // your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_gmail@gmail.com', 'UniVote');
            $mail->addAddress($user['email']);

            $mail->isHTML(true);
            $mail->Subject = 'UniVote Verification Code';
            $mail->Body = "Hello {$first_name},<br><br>
                Your UniVote verification code is: <b>{$otp}</b><br><br>
                This code will expire in <i>{$expires_in} minutes</i>.<br><br>
                &#9888; If you did not request this code, please ignore this email or contact your administrator immediately.<br><br>
                Thank you,<br>
                UniVote Team";

            $mail->send();
            $error_message = "A new code has been sent to your email.";
        } catch (Exception $e) {
            $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="two-fa-container">
    <div class="modal-content position-relative">
        <h2>Two-Factor Authentication</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center py-2"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="otp">Enter the code sent to your email:</label>
            <input type="text" name="otp" id="otp" maxlength="6" required class="form-control mb-3">
            <button type="submit" name="verify_2fa" class="btn btn-success w-100">Verify</button>
        </form>
        <form method="post" action="" style="margin-top:10px;">
            <button type="submit" name="resend" class="btn btn-secondary w-100">Resend Code</button>
        </form>
        <a href="login.php" class="btn btn-link w-100 mt-2">Back to Login</a>
    </div>
</div>
</body>
</html>