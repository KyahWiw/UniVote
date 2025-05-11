<?php
session_start();
include "../Manage Candidates/connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];
    $position_name = $_POST['position_name'];
    $position_order = $_POST['position_order'];

    try {
        // Insert position into the database
        $query = $db->prepare("INSERT INTO positions (event_id, position_name, position_order) VALUES (?, ?, ?)");
        $query->execute([$event_id, $position_name, $position_order]);

        $_SESSION['success_message'] = "Position added successfully.";
        header("Location: manage_positions.php?event_id=" . urlencode($event_id));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding position: " . $e->getMessage();
        header("Location: manage_positions.php?event_id=" . urlencode($event_id));
        exit();
    }
}
?> 