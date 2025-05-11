<?php
session_start();
include "connect.php";

// Check if the user is logged in
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "electoral_manager") {
    header("Location: ../../Login page/Login/index.php");
    exit();
}

// Retrieve the user's name from the database
if (isset($_SESSION["student_id"])) {
    $student_id = $_SESSION["student_id"];
    $query = $db->prepare("SELECT firstname, lastname FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
        $greetings_user_name = $user['firstname'];
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

// Fetch archived events
$query = $db->prepare("SELECT * FROM election_events WHERE is_archived = 1 ORDER BY event_datetime_start ASC");
$query->execute();
$archived_events = $query->fetchAll(PDO::FETCH_ASSOC);

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
    <a href="../Homepage/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
        <img class="logo" src="..\..\Assets\Image\logo-orig-white.png" alt="UniVote Logo">
    </a>
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
               <li><a href="../Homepage/index.php">Homepage</a></li>
               <li><a href="../Manage Election/index.php">Manage Election Events</a></li>
               <li class="active"><a href="#">Archived Election Events</a></li>
               <li><a href="-">Settings</a></li>
               <li><a href="../../Login page/Login/logout.php">Log Out</a></li>
            </ul>
        </nav>';
    ?>
    <hr>
        <div class="notifications">
            <h3>Notifications</h3>
            <p> Not Available Yet </p>
        </div>
    </aside>

    <main>
    <div class="search-bar">
        <input type="text" placeholder="Search">
        <button id="search-btn">🔍</button>
        <button class="add-btn" onclick="openModal('addEventModal')">➕ Add New Event</button>
    </div>
    
    <div class="main-content">
            <h2>Archived Election Events</h2>
            <?php if (empty($archived_events)): ?>
                <p>No archived events available.</p>
            <?php else: ?>
                <div class="event-cards-container">
                    <?php foreach ($archived_events as $event): ?>
                        <div class="event-card">
                            <div class="event-info">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p class="event-datetime">
                                    <strong>Start:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_start']))); ?>
                                    <br>
                                    <strong>End:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_end']))); ?>
                                </p>
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                <form action="restore_event.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                    <button type="submit" class="restore-btn">Restore</button>
                                </form>
                            </div>
                        </div>
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