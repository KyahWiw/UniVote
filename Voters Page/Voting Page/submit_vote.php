<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $student_id = $_SESSION['student_id'];

    // Save votes for each position
    foreach ($_POST as $position => $candidate_id) {
        if ($position !== 'event_id') {
            $query = $db->prepare("INSERT INTO votes (event_id, student_id, position, candidate_no) VALUES (?, ?, ?, ?)");
            $query->execute([$event_id, $student_id, $position, $candidate_id]);
        }
    }

    // Redirect back to the voting page to trigger the confirmation modal
    header("Location: index.php?event_id=$event_id&vote_submitted=true");
    exit();
}
?>