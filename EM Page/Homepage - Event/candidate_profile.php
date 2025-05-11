<?php
session_start();
include "../Manage Candidates/connect.php";

// Fetch the logged-in user's name
$header_user_name = "User";
if (isset($_SESSION["student_id"])) {
    $student_id = $_SESSION["student_id"];
    $query = $db->prepare("SELECT firstname, lastname FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
    }
}

// Check if candidate_no is provided
if (!isset($_GET['candidate_no']) || empty($_GET['candidate_no'])) {
    echo "No candidate selected.";
    exit();
}

$candidate_no = $_GET['candidate_no'];

// Fetch candidate details
$query = $db->prepare("SELECT * FROM candidates WHERE candidate_no = ?");
$query->execute([$candidate_no]);
$candidate = $query->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    echo "Candidate not found.";
    exit();
}

$event_id = isset($candidate['event_id']) ? $candidate['event_id'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile | <?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></title>
    <link rel="stylesheet" href="style.css">
    

</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
                <img class="logo" src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo">
            </a>
        </div>
        <div class="user-info">
            <span><?php echo htmlspecialchars($header_user_name); ?></span>
            <div class="user-avatar"></div>
        </div>
    </header>
    <div class="candidate-header">
        <div class="cover-photo"></div>
        <div class="profile-info">
            <img class="profile-picture" src="<?php echo htmlspecialchars('../../EM Page/Manage Candidates/' . $candidate['candidate_picture']); ?>" alt="Candidate Picture">
            <div>
                <h1><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></h1>
                <div class="profile-meta-main">
                    <?php
                        echo htmlspecialchars(
                            isset($candidate['position']) ? $candidate['position'] :
                            (isset($candidate['POSITION']) ? $candidate['POSITION'] :
                            (isset($candidate['position_name']) ? $candidate['position_name'] : 'Unknown'))
                        );
                    ?> | <?php echo htmlspecialchars($candidate['partylist']); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="candidate-main">
        <aside class="candidate-sidebar">
            <ul>
                <li class="active" onclick="showTab('overview')">Overview</li>
                <li onclick="showTab('achievements')">Achievements</li>
                <li onclick="showTab('education')">Education</li>
                <li onclick="showTab('contact')">Contact Info</li>
            </ul>
        </aside>
        <section class="candidate-content">
            <div id="tab-overview" class="tab-content active">
                <h2>Overview</h2>
                <p><strong>About:</strong><br>
                <?php echo !empty($candidate['about']) ? nl2br(htmlspecialchars($candidate['about'])) : 'No additional information.'; ?></p>
            </div>
            <div id="tab-achievements" class="tab-content">
                <h2>Achievements</h2>
                <p><?php echo !empty($candidate['achievements']) ? nl2br(htmlspecialchars($candidate['achievements'])) : 'No achievements listed.'; ?></p>
            </div>
            <div id="tab-education" class="tab-content">
                <h2>Education</h2>
                <p>Not provided.</p>
            </div>
            <div id="tab-contact" class="tab-content">
                <h2>Contact Info</h2>
                <p>Not provided.</p>
            </div>
            <a class="back-btn" href="index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">&larr; Back to Event</a>
        </section>
    </div>
    <script>
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.querySelectorAll('.candidate-sidebar li').forEach(el => el.classList.remove('active'));
        document.querySelector('.candidate-sidebar li[onclick*="' + tab + '"]').classList.add('active');
    }
    </script>
</body>
</html>
