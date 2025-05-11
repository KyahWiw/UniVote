<?php
session_start();
include "connect.php";

// Restrict access: Only Electoral Manager can access this page
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "electoral_manager") {
    header("Location: ../../Login page/Login/index.php");
    exit();
}

if ($_SESSION["role"] === "electoral_manager" && !isset($_SESSION['2fa_verified'])) {
    header("Location: 2fa.php");
    exit();
}

// Retrieve the user's name from the database
if (isset($_SESSION["student_id"])) {
    $student_id = $_SESSION["student_id"];
    $query = $db->prepare("SELECT firstname, lastname, department FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
        $greetings_user_name = $user['firstname'];
        $user_department = $user['department']; // Fetch the user's department
    } else {
        $header_user_name = "User"; // Default value if user is not found
    }
} else {
    $header_user_name = "User"; // Default value if session is not set
}

// Fetch election events from the database
$query = $db->prepare("SELECT * FROM election_events ORDER BY event_datetime_start ASC");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage | UniVote </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">
        <img class="logo" src="..\..\Assets\Image\logo-orig-white.png">
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($header_user_name); ?></span>
        <div class="user-avatar"></div>
    </div>
</header>

<div class="container">
    <aside>
    <?php
        echo '<nav>
        <nav>
            <h3>Quick Access</h3>
            <ul>
                <li class="active"><a href="#">Homepage</a></li>
                <li><a href="../Manage Election/index.php">Manage Election Events</a></li>
                <li><a href="../Archive Election/index.php">Archived Election Events</a></li>
                <li><a href="-">Settings</a></li>
                <li><a href="../../Login page/Login/logout.php">Log Out</a></li>
            </ul>
        </nav>';
    ?>
        <hr>
        <div class="notifications">
            <h3>Notifications</h3>
            <p> Not Yet Available </p>
        </div>
    </aside>

    <main>
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <button id="search-btn">🔍</button>
        </div>

        <section class="greeting-card">
                <span class="calendar-icon">📅</span>
                <span id="liveTime"></span>
            <h2>Greetings, <?php echo htmlspecialchars($greetings_user_name); ?>!</h2>
        </section>

        <div class="events">
    <h2>Election Events</h2>
    <?php if (empty($events)): ?>
        <p>No election events yet. Click "Add New Event" to create one.</p>
    <?php else: ?>
        <div class="event-cards-container">
            <?php foreach ($events as $event): ?>
                <a href="../Homepage - Event/index.php?event_id=<?php echo htmlspecialchars($event['id']); ?>" class="event-card-link">
                    <div class="event-card">
                        <div class="event-banner-container">
                            <img src="<?php echo htmlspecialchars('../../EM Page/Manage Election/' . $event['event_banner']); ?>" 
                            alt="<?php echo htmlspecialchars($event['event_name']); ?> Event Banner" class="event-banner">
                        </div>
                        <div class="event-info">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <p class="event-datetime">
                                <strong>Start:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_start']))); ?> 
                                <br>
                                <strong>End:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_end']))); ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
    </main>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <img src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo" class="footer-logo">
        <p>&copy; 2025 UniVote. All rights reserved.</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>