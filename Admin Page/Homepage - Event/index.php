<?php
session_start();
include "connect.php";
include "fetch_event_banner.php";

// Check if the user is logged in
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
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

// Check if event_id is provided in the URL
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "No election event selected.";
    exit();
}

$event_id = $_GET['event_id'];

// Fetch the election event details
$query = $db->prepare("SELECT * FROM election_events WHERE id = ?");
$query->execute([$event_id]);
$event = $query->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "Election event not found.";
    exit();
}

// Fetch candidates for the specific election event
$query = $db->prepare("SELECT * FROM candidates WHERE event_id = ? ORDER BY position ASC");
$query->execute([$event_id]);
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);
$banner_path = getEventBanner($db, $event_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage | <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="../Homepage/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
            <img class="logo" src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo">
        </a>
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($header_user_name); ?></span>
        <div class="user-avatar"></div>
    </div>
</header>

<div class="container">
    <aside>
        <nav>
            <h3>Quick Access</h3>
            <ul>
                <li class="active"><a href="#">Homepage</a></li>
                <li><a href="../Election Event/index.php">Election Events</a></li>
                <li><a href="../Voting Result Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Election Result</a></li>
                <li><a href="../Settings - Event/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Settings</a></li>
                <li><a href="../../Login page/Login/logout.php">Log Out</a></li>
            </ul>
        </nav>
        <hr>
        <div class="notifications">
            <h3>Notifications</h3>
            <p> Not Yet Available </p>
        </div>
    </aside>

    <main>
    <section class="event-details" style="background-image: url('<?php echo !empty($event['event_banner']) && file_exists('../../EM Page/Manage Election/' . $event['event_banner']) 
    ? htmlspecialchars('../../EM Page/Manage Election/' . $event['event_banner']) 
    : '../../Assets/Image/default-banner.jpg'; ?>');">
    <div class="event-details-overlay">
        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
        <p><?php echo htmlspecialchars($event['description']); ?></p>
        <p><strong>Organizer:</strong> <?php echo htmlspecialchars($event['organized_by']); ?></p>
        <p><strong>Voting Start:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_start']))); ?></p>
        <p><strong>Voting Ends:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i A", strtotime($event['event_datetime_end']))); ?></p>
    </div>
</section>
<hr>
<section class="candidates">
    <h3>Candidates</h3>
    <div class="positions-wrapper">
        <div class="positions-container">
            <?php
            // Group candidates by position
            $grouped_candidates = [];
            foreach ($candidates as $candidate) {
                $grouped_candidates[$candidate['POSITION']][] = $candidate;
            }

            $position_order = ['President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor', 'PRO', 'Other'];

            uksort($grouped_candidates, function ($a, $b) use ($position_order) {
                $index_a = array_search($a, $position_order);
                $index_b = array_search($b, $position_order);

                // If a position is not in the predefined order, place it at the end
                $index_a = $index_a === false ? PHP_INT_MAX : $index_a;
                $index_b = $index_b === false ? PHP_INT_MAX : $index_b;

                return $index_a - $index_b;
            });

            // Render each position and its candidates
            foreach ($grouped_candidates as $position => $candidates_in_position): ?>
                <div class="position-group">
                    <h4><?php echo htmlspecialchars($position); ?></h4>
                    <div class="candidate-cards">
                        <?php foreach ($candidates_in_position as $candidate): ?>
                            <div class="candidate-card">
                                <img src="<?php echo htmlspecialchars('../../EM Page/Manage Candidates/' . $candidate['candidate_picture']); ?>" 
                                    alt="Candidate Picture" class="candidate-picture">
                                <p><strong><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></strong></p>
                                <p><em><?php echo htmlspecialchars($candidate['partylist']); ?></em></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="scroll-previous">&lt;</button> <!-- Previous Button -->
        <button class="scroll-next">&gt;</button> <!-- Next Button -->
    </div>
</section>
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