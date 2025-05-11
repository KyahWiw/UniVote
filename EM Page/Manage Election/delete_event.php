<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];

    try {
        // Delete the election event (cascading deletions will handle related data)
        $query = $db->prepare("DELETE FROM election_events WHERE id = ?");
        $query->execute([$event_id]);

        $_SESSION['success_message'] = "Election event and all associated data have been deleted successfully.";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting event: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}
?>