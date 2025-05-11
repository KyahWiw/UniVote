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

// Fetch the event details
$event_query = $db->prepare("SELECT * FROM election_events WHERE id = ?");
$event_query->execute([$event_id]);
$event = $event_query->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "Error: Event not found.";
    exit();
}

$query = $db->prepare("SELECT firstname, lastname, role FROM accounts WHERE student_id = ?");
$query->execute([$student_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
    $user_role = $user['role']; // Fetch the user's role
} else {
    header("Location: ../../Login page/Login/index.php"); // Redirect if user not found
    exit();
}


?>

<!DOCTYPE html>
<ht lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Page | <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="../Homepage/index.php">
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
                <li><a href="../Report Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>"">Election Result</a></li>
                <li class="active"><a href="#">Report Page</a></li>
                <li><a href="../Settings - Event/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Settings</a></li>
            </ul>
        </nav>
        <hr>
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
    <h1><?php echo htmlspecialchars($event['event_name']); ?></h1> <!-- Event Title -->
    <p class="subtitle">
        This report provides a real-time summary of the voting results for the event 
        "<strong><?php echo htmlspecialchars($event['event_name']); ?></strong>", organized by 
        "<strong><?php echo htmlspecialchars($event['organized_by']); ?></strong>".
    </p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Summary</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Partial and Unofficial Results</td>
                <td>As of <?php echo date("h:i A, F d, Y"); ?></td>
            </tr>
            <tr>
                <td>Total Votes Processed</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>Total Votes In</td>
                <td>0,000</td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($results)): ?>
        <?php 
        $current_position = null;
        foreach ($results as $index => $result): 
            if ($current_position !== $result['position']):
                if ($current_position !== null): ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($result['position']); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Name</th>
                            <th>Partylist</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php 
                $current_position = $result['position'];
                $rank = 1; // Reset rank for the new position
            endif; ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($result['candidate_firstname'] . ' ' . $result['candidate_lastname']); ?></td>
                <td><?php echo htmlspecialchars($result['partylist_name']); ?></td>
                <td><?php echo htmlspecialchars($result['vote_count']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    <?php else: ?>
        <p>No votes have been cast yet.</p>
    <?php endif; ?>

    <hr>
    <div class="report-footer">
    <p><strong>Verified By:</strong> <?php echo htmlspecialchars($header_user_name); ?> (<?php echo ucfirst($user_role); ?>)</p>
        <p><strong>Date/Time Generated:</strong> <?php echo date("F d, Y h:i A"); ?></p>
    </div>
    <button class="print-btn" onclick="window.print()">Print Report</button> <!-- Print Button -->
    <img src="../../Assets/logo-orig-black.png" alt="New Logo" class="print-logo" style="display: none;"> <!-- New Logo for Printing -->
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