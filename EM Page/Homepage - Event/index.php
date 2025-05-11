<?php
session_start();
include "connect.php";
include "fetch_event_banner.php";

// Prevent browser from caching the login page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

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

// Fetch positions for this event in the correct order
$positions_query = $db->prepare("SELECT position_name FROM positions WHERE event_id = ? ORDER BY position_order ASC");
$positions_query->execute([$event_id]);
$positions = $positions_query->fetchAll(PDO::FETCH_COLUMN);

// Use this array for ordering
$position_order = $positions;

// Group candidates by position
$grouped_candidates = [];
foreach ($candidates as $candidate) {
    $grouped_candidates[$candidate['POSITION']][] = $candidate;}

uksort($grouped_candidates, function ($a, $b) use ($position_order) {
    $index_a = array_search($a, $position_order);
    $index_b = array_search($b, $position_order);

    // If a position is not in the predefined order, place it at the end
    $index_a = $index_a === false ? PHP_INT_MAX : $index_a;
    $index_b = $index_b === false ? PHP_INT_MAX : $index_b;

    return $index_a - $index_b;
});
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
                <li><a href="../Manage Candidates/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Manage Candidates</a></li>
                <li><a href="../Manage Partylist/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Manage Partylist</a></li>
                <li><a href="../Voting Results/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Election Result</a></li>
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
    <h4>Positions</h4>
    <div class="positions-wrapper">
        <div class="positions-container">
            <?php
            // Loop through each position in the correct order
            foreach ($position_order as $position_name):
                if (!empty($grouped_candidates[$position_name])): 
                    // Sort candidates by last name within this position
                    usort($grouped_candidates[$position_name], function($a, $b) {
                        $a_last = isset($a['lastname']) ? $a['lastname'] : (isset($a['LASTNAME']) ? $a['LASTNAME'] : '');
                        $b_last = isset($b['lastname']) ? $b['lastname'] : (isset($b['LASTNAME']) ? $b['LASTNAME'] : '');
                        return strcmp($a_last, $b_last);
                    });
                ?>
                    <div class="position-group">
                        <h4><?php echo htmlspecialchars($position_name); ?></h4>
                        <div class="candidate-cards">
                            <?php foreach ($grouped_candidates[$position_name] as $idx => $candidate): ?>
                                <div class="candidate-card">
                                    <img src="<?php echo htmlspecialchars('../../EM Page/Manage Candidates/' . $candidate['candidate_picture']); ?>" 
                                        alt="Candidate Picture" class="candidate-picture">
                                    <p>
                                        <a href="candidate_profile.php?candidate_no=<?php echo $candidate['candidate_no']; ?>" class="profile-btn">
                                            <strong><?php echo ($idx+1) . '. ' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></strong>
                                        </a>
                                        <em style="margin-left:10px;"><?php echo htmlspecialchars($candidate['partylist']); ?></em>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            endforeach;
            ?>
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