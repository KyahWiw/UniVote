<?php
session_start();
// Only allow admin to access this page
if (
    !isset($_SESSION['pending_2fa']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../../Login page/Login/index.php");
    exit();
}

// PHPMailer includes (adjust path as needed)
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle resend
if (isset($_POST['resend'])) {
    // Generate new OTP
    $otp = rand(100000, 999999);
    $_SESSION['2fa_otp'] = $otp;
    $_SESSION['2fa_otp_expiry'] = time() + 300; // 5 minutes

    // Fetch email from database
    include "connect.php";
    $student_id = $_SESSION['student_id'];
    $query = $db->prepare("SELECT email FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['email'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jhongarapan@gmail.com'; // your Gmail address
            $mail->Password = 'degy ppco ofxr kutl';  // your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_gmail@gmail.com', 'UniVote');
            $mail->addAddress($user['email']);

            $mail->Subject = 'UniVote Verification Code';
            $mail->Body = "Hello {$first_name},
            Your UniVote verification code is: **{$otp}**
            This code will expire in *{$expires_in}* minutes.
            ⚠️ If you did not request this code, please ignore this email or contact your administrator immediately.
                
            Thank you,
            UniVote Team";

            $mail->send();
            $info = "A new code has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No email address found. Please contact the administrator.";
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $input_otp = $_POST['otp'];
    if (
        isset($_SESSION['2fa_otp'], $_SESSION['2fa_otp_expiry']) &&
        $_SESSION['2fa_otp'] == $input_otp &&
        time() < $_SESSION['2fa_otp_expiry']
    ) {
        // 2FA successful
        unset($_SESSION['pending_2fa'], $_SESSION['2fa_otp'], $_SESSION['2fa_otp_expiry']);
        $_SESSION['2fa_verified'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid or expired code.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="two-fa-container">
        <h2>Two-Factor Authentication</h2>
        <?php if (isset($error)) echo "<div class='message error'>{$error}</div>"; ?>
        <?php if (isset($info)) echo "<div class='message info'>{$info}</div>"; ?>
        <form method="post">
            <label for="otp">Enter the code sent to your email:</label>
            <input type="text" name="otp" id="otp" maxlength="6" autocomplete="one-time-code" required>
            <button type="submit">Verify</button>
        </form>
        <form method="post">
            <button type="submit" name="resend" class="resend-btn">Resend Code</button>
        </form>
    </div>
</body>