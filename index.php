<?php
include "connect.php";

// Default values for non-logged-in users
$header_user_name = "Guest";
$greetings_user_name = "Guest";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to UniVote</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="intro-header">
    <div class="logo">
        <img src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo" class="logo-img">
    </div>
    <nav class="nav-bar">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="Login page/Login/login.php">Login</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
    <h1>Welcome to UniVote</h1>
    <p>Your trusted platform for managing and participating in university elections.</p>
</header>

<main class="intro-main">
    <section id="about" class="about-section">
        <h2>About UniVote</h2>
        <p>
            UniVote is a secure and user-friendly platform designed to streamline the election process in universities. 
            Whether you're a voter, an electoral manager, or an administrator, UniVote provides the tools you need to 
            organize, manage, and participate in elections efficiently.
        </p>
    </section>

    <section id="features" class="features-section">
        <h2>Features</h2>
        <ul>
            <li>📋 Easy election event creation and management</li>
            <li>🔒 Secure and transparent voting process</li>
            <li>📊 Real-time results and analytics</li>
            <li>👥 Role-based access for administrators, electoral managers, and voters</li>
        </ul>
    </section>

    <section id="contact" class="contact-section">
        <h2>Contact Us</h2>
        <p>If you have any questions or need assistance, feel free to reach out to us at <a href="mailto:support@univote.com">support@univote.com</a>.</p>
    </section>
</main>

<footer class="intro-footer">
    <div class="footer-content">
        <img src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo" class="footer-logo">
        <p>&copy; 2025 UniVote. All rights reserved.</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>