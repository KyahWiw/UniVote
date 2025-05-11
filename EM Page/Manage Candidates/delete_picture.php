<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate inputs
    if (!isset($_POST['candidate_no']) || !isset($_POST['event_id'])) {
        $_SESSION['error_message'] = "Missing required parameters";
        header("Location: index.php");
        exit();
    }

    $candidate_no = $_POST['candidate_no'];
    $event_id = $_POST['event_id'];

    try {
        // Get current picture path
        $query = $db->prepare("SELECT candidate_picture FROM candidates WHERE candidate_no = ?");
        $query->execute([$candidate_no]);
        $picture_path = $query->fetchColumn();

        // Delete the picture file if it exists
        if ($picture_path && file_exists($picture_path)) {
            unlink($picture_path);
        }

        // Update database to remove picture reference
        $query = $db->prepare("UPDATE candidates SET candidate_picture = NULL WHERE candidate_no = ?");
        $query->execute([$candidate_no]);

        $_SESSION['success_message'] = "Picture deleted successfully";
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting picture: " . $e->getMessage();
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    }
}
?>
