<?php
session_start();
include "../Manage Candidates/connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];
    $position_id = $_POST['position_id'];
    $position_name = $_POST['position_name'];
    $position_order = $_POST['position_order'];

    try {
        // Update position in the database
        $query = $db->prepare("UPDATE positions SET position_name = ?, position_order = ? WHERE position_id = ? AND event_id = ?");
        $query->execute([$position_name, $position_order, $position_id, $event_id]);

        $_SESSION['success_message'] = "Position updated successfully.";
        header("Location: manage_positions.php?event_id=" . urlencode($event_id));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating position: " . $e->getMessage();
        header("Location: manage_positions.php?event_id=" . urlencode($event_id));
        exit();
    }
}
?> 