<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate event_id
    if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
        $_SESSION['error_message'] = "Error: Event ID is missing.";
        header("Location: index.php");
        exit();
    }

    $candidate_no = $_POST['candidate_no'];
    $event_id = $_POST['event_id'];

    try {
        // First get the picture path before deleting
        $query = $db->prepare("SELECT candidate_picture FROM candidates WHERE candidate_no = ?");
        $query->execute([$candidate_no]);
        $picture_path = $query->fetchColumn();

        // Delete the candidate record
        $query = $db->prepare("DELETE FROM candidates WHERE candidate_no = ?");
        $query->execute([$candidate_no]);

        // Delete the picture file if it exists
        if ($picture_path && file_exists($picture_path)) {
            unlink($picture_path);
        }

        // Reset auto-increment if needed
        $query = $db->prepare("SELECT COUNT(*) FROM candidates");
        $query->execute();
        $count = $query->fetchColumn();
        
        if ($count == 0) {
            $db->exec("ALTER TABLE candidates AUTO_INCREMENT = 1");
        }

        // Redirect back to the Manage Candidates page with a success message
        $_SESSION['success_message'] = "Candidate deleted successfully.";
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    } catch (PDOException $e) {
        // Redirect back with an error message
        $_SESSION['error_message'] = "Error deleting candidate: " . $e->getMessage();
        header("Location: index.php?event_id=" . urlencode($event_id));
        exit();
    }
}
?>
