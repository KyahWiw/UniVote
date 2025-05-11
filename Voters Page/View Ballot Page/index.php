<?php
// Fetch positions
$query = $db->prepare("SELECT DISTINCT position FROM candidates ORDER BY position ASC");
$query->execute();
$positions = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch candidates
$query = $db->prepare("SELECT * FROM candidates ORDER BY position ASC");
$query->execute();
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Your Ballot | UniVote </title>
    <link rel="stylesheet" href="style.css" />
  </head>

  <body>
  <header>
   <div class="logo">
    <a href="../Homepage/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
        <img class="logo" src="..\..\Assets\Image\logo-orig-white.png" alt="UniVote Logo">
    </a>
    </div>
    <div class="user-info">
        <span>Username</span>
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
                <li><a href="candidates.php">About Candidate</a></li>
                <li><a href="partylist.php">About Partylist</a></li>
                <li><a href="../Voting Page/index.php">Election Event</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </nav>';
    ?>

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

    
  </body>
</html>
