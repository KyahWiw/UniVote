<?php
session_start();
include "connect.php";

// Check if the user is logged in
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "voter") {
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
// Fetch positions
$query = $db->prepare("SELECT DISTINCT position FROM candidates ORDER BY position ASC");
$query->execute();
$positions = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch candidates
$query = $db->prepare("SELECT * FROM candidates ORDER BY position ASC");
$query->execute();
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch Election Event
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Error: No election event selected.";
    exit();
}
$event_id = $_GET['event_id'];

$query = $db->prepare("SELECT * FROM candidates ORDER BY position ASC");
$query->execute();
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch positions in hierarchical order
$query = $db->prepare("
    SELECT *
    FROM candidates 
    ORDER BY 
        CASE 
            WHEN position = 'President' THEN 1
            WHEN position = 'Vice President' THEN 2
            WHEN position = 'Secretary' THEN 3
            WHEN position = 'Treasurer' THEN 4
            WHEN position = 'Auditor' THEN 5
            ELSE 6
        END,
         lastname ASC
");
$query->execute();
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);

// Positioning the Last Names
$query = $db->prepare("
    SELECT DISTINCT position 
    FROM candidates 
    WHERE event_id = ?
    ORDER BY 
        CASE 
            WHEN position = 'President' THEN 1
            WHEN position = 'Vice President' THEN 2
            WHEN position = 'Secretary' THEN 3
            WHEN position = 'Treasurer' THEN 4
            WHEN position = 'Auditor' THEN 5
            ELSE 6
        END
");
$query->execute([$event_id]);
$positions = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniVote - Election</title>
    <link rel="stylesheet" href="style.css">
</head>
<>

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
        <li><a href="../Candidates Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Candidates</a></li>
        <li class="active"><a href="../Voting Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Vote Now</a></li>
        <li><a href="../Voting Result Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Election Results</a></li>
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

    <!-- Main Content -->
<main>
    <div class="main-content">
        <h2>Vote for Your Candidates</h2>
        <p>One (1) vote per candidate per position.</p>
        <hr>
        <!-- Tab Navigation for Positions -->
        <div class="position-tabs">
            <?php foreach ($positions as $index => $position): ?>
                <button class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showPosition('<?php echo htmlspecialchars($position['position']); ?>')">
                    <?php echo htmlspecialchars($position['position']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Candidate Cards for Each Position -->
        <form id="voteForm" method="POST" action="submit_vote.php">
    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
    <div class="positions-container">
        <?php foreach ($positions as $index => $position): ?>
            <div class="position-group <?php echo $index === 0 ? 'active' : ''; ?>" id="<?php echo htmlspecialchars($position['position']); ?>">
                <h3><?php echo htmlspecialchars($position['position']); ?></h3>
                <div class="candidate-cards">
                    <?php foreach ($candidates as $candidate): ?>
                        <?php if ($candidate['POSITION'] === $position['position']): ?>
                            <div class="candidate-card">
                            <img src="<?php echo htmlspecialchars('../../EM Page/Manage Candidates/' . $candidate['candidate_picture']); ?>" 
                            alt="Candidate Picture" class="candidate-picture">
                                <p><strong><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></strong></p>
                                <p class="partylist-name"><?php echo htmlspecialchars($candidate['partylist']); ?></p>
                                <input type="radio" 
                                       name="<?php echo htmlspecialchars($position['position']); ?>" 
                                       value="<?php echo htmlspecialchars($candidate['candidate_no']); ?>" required>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="submit-btn" onclick="openDoubleCheckModal()">Submit Vote</button>
</form>
</div>
</main>
</div>

<!-- Double-Check Modal -->
<div id="doubleCheckModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Your Votes</h2>
        <p> Double Check your votes before submitting.</p>
        <hr>
        <div id="selectedCandidates">
            <!-- Selected candidates will be dynamically added here -->
        </div>
        <div class="modal-actions">
        <button type="button" class="submit-btn" onclick="confirmVote()">Submit Vote</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <h2>Thank You!</h2>
        <p>Your vote was submitted successfully.</p>
        <button type="button" class="confirm-btn" onclick="redirectToResults()">Go to Election Results</button>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <img src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo" class="footer-logo">
        <p>&copy; 2025 UniVote. All rights reserved.</p>
    </div>
</footer>


    <script src="script.js"></script>
    <?php if (isset($_GET['vote_submitted']) && $_GET['vote_submitted'] === 'true'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        openConfirmationModal();
    });
</script>
<?php endif; ?>
</body>
</html>