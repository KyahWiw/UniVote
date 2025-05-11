<?php
session_start();
include "connect.php";

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
    } else {
        $header_user_name = "User"; // Default value if user is not found
    }
} else {
    $header_user_name = "User"; // Default value if session is not set
}

// Check if an election event is selected
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Error: No election event selected.";
    exit();
}

$event_id = $_GET['event_id'];

// Fetch vote counts grouped by position and candidate
$query = $db->prepare("
    SELECT 
        c.position,
        c.firstname AS candidate_firstname,
        c.lastname AS candidate_lastname,
        c.partylist AS partylist_name,
        COUNT(v.candidate_no) AS vote_count
    FROM candidates c
    LEFT JOIN votes v ON c.candidate_no = v.candidate_no AND v.event_id = ?
    WHERE c.event_id = ?
    GROUP BY c.position, c.candidate_no
    ORDER BY 
        CASE 
            WHEN c.position = 'President' THEN 1
            WHEN c.position = 'Vice President' THEN 2
            WHEN c.position = 'Secretary' THEN 3
            WHEN c.position = 'Treasurer' THEN 4
            WHEN c.position = 'Auditor' THEN 5
            ELSE 6
        END,
        vote_count DESC
");
$query->execute([$event_id, $event_id]);  
$results = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results | UniVote</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="../Homepage (Php)/index.php">
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
        <nav>
            <h3>Quick Access</h3>
            <ul>
                <li><a href="../Homepage - Event/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Homepage</a></li>
                <li><a href="../Election Event/index.php">Election Events</a></li>
                <li class="active"><a href="#">Election Result</a></li>
                <li><a href="../Report Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Report Page</a></li>
                <li><a href="../Settings - Event/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Settings</a></li>
            </ul>
        </nav>

        <div class="notifications">
            <h3>Notifications</h3>
            <ul>
                <li><span class="bell-icon">🔔</span> Notification 1</li>
                <li><span class="bell-icon">🔔</span> Notification 2</li>
                <li><span class="bell-icon">🔔</span> Notification 3</li>
                <li><span class="bell-icon">🔔</span> Notification 4</li>
            </ul>
        </div>
    </aside>

    <main>
        <h1>Election Results</h1>
        <p class="subtitle">This is a Real-Time Voting Result Gathering.</p>

        <div class="result-summary">
            <div class="summary-item">
                <h3>Partial and Unofficial Results</h3>
                <p>As of <?php echo date("h:i A, F d, Y"); ?></p>
            </div>
            <div class="summary-item">
                <h3>Total Votes Processed</h3>
                <p>0%</p>
            </div>
            <div class="summary-item">
                <h3>Total Votes In</h3>
                <p>0,000</p>
            </div>
        </div>

        <?php if (!empty($results)): ?>
    <?php 
    $current_position = null;
    $rank = 1; // Initialize rank
    foreach ($results as $index => $result): 
        if ($current_position !== $result['position']):
            if ($current_position !== null): ?>
                </ul> <!-- Close previous position group -->
            <?php endif; ?>
            <div class="position-group">
                <h2><?php echo htmlspecialchars($result['position']); ?></h2>
                <p class="timestamp">As of <?php echo date("F d, Y h:i A"); ?></p>
                <ul>
            <?php 
            $current_position = $result['position'];
            $rank = 1; // Reset rank for the new position
        endif; ?>
        <li>
            <span class="candidate-rank"><?php echo $rank++; ?></span> <!-- Display rank -->
            <div class="candidate-info">
                <strong><?php echo htmlspecialchars($result['candidate_firstname'] . ' ' . $result['candidate_lastname']); ?></strong>
                <small>(<?php echo htmlspecialchars($result['partylist_name']); ?>)</small>
            </div>
            <div class="candidate-votes">
                <span class="votes"><?php echo htmlspecialchars($result['vote_count']); ?> votes</span>
                <div class="vote-bar">
                    <div class="vote-bar-fill" style="width: <?php echo ($result['vote_count'] / 100) * 100; ?>%;"></div>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
    </ul> <!-- Close last position group -->
<?php else: ?>
    <p>No votes have been cast yet.</p>
<?php endif; ?>
    </main>
</div>

<script src="script.js"></script>

</body>
</html>