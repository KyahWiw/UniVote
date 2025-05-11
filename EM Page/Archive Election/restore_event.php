<?php
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];

    // Mark the event as active
    $query = $db->prepare("UPDATE election_events SET is_archived = 0 WHERE id = ?");
    $query->execute([$event_id]);

    header("Location: archived_events.php");
    exit();
}
?>